<?php

namespace App\Controller\Admin;

use App\Entity\PostCategory;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\ColorField;
use EasyCorp\Bundle\EasyAdminBundle\Field\ImageField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;

class PostCategoryCrudController extends AbstractCrudController
{
    public static function getEntityFqcn(): string
    {
        return PostCategory::class;
    }

    public function configureCrud(Crud $crud): Crud
    {
        return $crud->setEntityPermission('ROLE_SUPER_ADMIN');
    }

    public function configureFields(string $pageName): iterable
    {
        return [
            TextField::new('name'),
            ColorField::new('color'),
            ImageField::new('image', 'Image (300x168px)')
                ->setBasePath('uploads/categories/')
                ->setUploadDir('public/uploads/categories/')
                ->setUploadedFileNamePattern('[slug]-[timestamp].[extension]')
                ->setSortable(false)
        ];
    }
}
