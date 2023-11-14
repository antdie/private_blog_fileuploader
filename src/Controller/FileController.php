<?php

namespace App\Controller;

use App\Entity\File;
use App\Form\FileFormType;
use App\Repository\FileRepository;
use Doctrine\ORM\EntityManagerInterface;
use Knp\Component\Pager\PaginatorInterface;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\HttpFoundation\File\Exception\FileException;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\HeaderUtils;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
// Symfony\Component\HttpFoundation\ResponseHeaderBag required ? https://symfony.com/doc/6.1/components/http_foundation.html#serving-files
use Symfony\Component\HttpFoundation\ResponseHeaderBag;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\String\Slugger\SluggerInterface;

#[Route('/files')]
class FileController extends AbstractController
{
    #[Route('/{!page<\d+>?1}', name: 'app_file')]
    public function index(int $page, FileRepository $fileRepository, PaginatorInterface $paginator): Response
    {
        $query = $fileRepository->queryForPagination($this->isGranted('ROLE_USER'));

        $files = $paginator->paginate(
            $query, /* query NOT result */
            $page, /*page number*/
            $this->getParameter('app.files_per_page') /*limit per page*/
        );

        return $this->render('file/index.html.twig', [
            'controller_name' => 'FileController',
            'files' => $files
        ]);
    }

    #[Route('/{name}/{id}', name: 'app_file_show')]
    #[ParamConverter('file', options: ['mapping' => ['name' => 'name', 'id' => 'id']])]
    public function show(File $file, FileRepository $fileRepository): Response
    {
        if ($file->isPrivate() && !$this->isGranted('ROLE_USER')) {
            throw $this->createNotFoundException();
        }

        $next = $fileRepository->findNextFile($file, $this->isGranted('ROLE_USER'));
        $prev = $fileRepository->findPreviousFile($file, $this->isGranted('ROLE_USER'));

        $txtContent = null;
        if ($file->getType() === 'txt') {
            $txtContent = file_get_contents($this->getParameter('kernel.project_dir').'/public/uploads/files/'.$file->getName().'-'.$file->getHash().'.'.$file->getType());
        }

        return $this->render('file/show.html.twig', [
            'controller_name' => 'FileController',
            'file' => $file,
            'next' => $next,
            'prev' => $prev,
            'txt_content' => $txtContent
        ]);
    }

    #[Route('/add', name: 'app_file_add')]
    public function add(Request $request, SluggerInterface $slugger, EntityManagerInterface $entityManager): Response
    {
        $file = new File();
        $form = $this->createForm(FileFormType::class, $file);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            /** @var UploadedFile $uploadedFile */
            $uploadedFile = $form->get('file')->getData();

            $originalFilename = pathinfo($uploadedFile->getClientOriginalName(), PATHINFO_FILENAME);
            // this is needed to safely include the file name as part of the URL
            $safeFilename = $slugger->slug($originalFilename)->lower();
            $hash = uniqid();
            $type = $uploadedFile->guessExtension();
            $size = $uploadedFile->getSize();

            // Move the file to the directory where brochures are stored
            try {
                $uploadedFile->move(
                    $this->getParameter('kernel.project_dir').'/public/uploads/files',
                    $safeFilename.'-'.$hash.'.'.$uploadedFile->guessExtension()
                );
            } catch (FileException $e) {
                // ... handle exception if something happens during file upload
            }

            $file->setName($safeFilename)
                ->setHash($hash)
                ->setType($type)
                ->setSize($size)
                ->setAccount($this->getUser());
            $entityManager->persist($file);
            $entityManager->flush();

            $this->addFlash('success', 'Your file has been uploaded.');
            return $this->redirectToRoute('app_file_show', ['name' => $file->getName(), 'id' => $file->getId()]);
        }

        return $this->renderForm('file/add.html.twig', [
            'controller_name' => 'FileController',
            'form' => $form
        ]);
    }

    #[Route('/download/{name}/{id}', name: 'app_file_download')]
    #[ParamConverter('file', options: ['mapping' => ['name' => 'name', 'id' => 'id']])]
    public function download(File $file, EntityManagerInterface $entityManager): Response
    {
        if ($file->isPrivate() && !$this->isGranted('ROLE_USER')) {
            throw $this->createNotFoundException();
        }

        $content = file_get_contents($this->getParameter('kernel.project_dir').'/public/uploads/files/'.$file->getName().'-'.$file->getHash().'.'.$file->getType());
        if (!$content) {
            $entityManager->remove($file);
            $entityManager->flush();

            $this->addFlash('error', 'Sorry, we couldn\'t find this file.');
            return $this->redirectToRoute('app_file');
        }

        $response = new Response($content);

        $disposition = HeaderUtils::makeDisposition(
            HeaderUtils::DISPOSITION_ATTACHMENT,
            $file->getName().'.'.$file->getType()
        );

        $response->headers->set('Content-Disposition', $disposition);

        return $response;
    }

    #[Route('/modify/{name}/{id}', name: 'app_file_modify')]
    #[ParamConverter('file', options: ['mapping' => ['name' => 'name', 'id' => 'id']])]
    public function modify(File $file, EntityManagerInterface $entityManager): Response
    {
        if (
            $file->getAccount() !== $this->getUser() &&
            !$this->isGranted('ROLE_ADMIN')
        ) {
            throw $this->createAccessDeniedException();
        }

        if ($file->isPrivate()) {
            $this->addFlash('success', 'Your file is now public.');
            $file->setPrivate(false);
        } else {
            $this->addFlash('error', 'Your file is now private.');
            $file->setPrivate(true);
        }

        $entityManager->flush();

        return $this->redirectToRoute('app_file_show', [
            'name' => $file,
            'id' => $file->getId()
        ]);
    }

    #[Route('/delete/{name}/{id}', name: 'app_file_delete')]
    #[ParamConverter('file', options: ['mapping' => ['name' => 'name', 'id' => 'id']])]
    public function delete(File $file, EntityManagerInterface $entityManager): Response
    {
        if (
            $file->getAccount() !== $this->getUser() &&
            !$this->isGranted('ROLE_ADMIN')
        ) {
            throw $this->createAccessDeniedException();
        }

        $filePath = $this->getParameter('kernel.project_dir').'/public/uploads/files/'.$file->getName().'-'.$file->getHash().'.'.$file->getType();
        $filesystem = new Filesystem();
        if ($filesystem->exists($filePath)) {
            $filesystem->remove($filePath);
        }

        $entityManager->remove($file);
        $entityManager->flush();

        $this->addFlash('success', 'File successfully deleted.');
        return $this->redirectToRoute('app_file');
    }
}
