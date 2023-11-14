<?php

namespace App\Controller\Admin;

use App\Entity\Post;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\AssociationField;
use EasyCorp\Bundle\EasyAdminBundle\Field\DateTimeField;
use EasyCorp\Bundle\EasyAdminBundle\Field\ImageField;
use EasyCorp\Bundle\EasyAdminBundle\Field\SlugField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextEditorField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;

class PostCrudController extends AbstractCrudController
{
    public static function getEntityFqcn(): string
    {
        return Post::class;
    }

    public function configureCrud(Crud $crud): Crud
    {
        return $crud->setDefaultSort(['id' => 'DESC']);
    }

    public function configureFields(string $pageName): iterable
    {
        return [
            TextField::new('title'),
            SlugField::new('slug')->setTargetFieldName('title')->hideOnIndex(),
            TextEditorField::new('content')->setNumOfRows(15)->hideOnIndex(),
            DateTimeField::new('date')->hideOnForm(),
            AssociationField::new('category')->setRequired(true)->renderAsNativeWidget(),
            AssociationField::new('postComments')->hideOnForm(),
            ImageField::new('image', 'Image (1200x215px)')
                ->setBasePath('uploads/posts/')
                ->setUploadDir('public/uploads/posts/')
                ->setUploadedFileNamePattern('[slug]-[timestamp].[extension]')
                ->setSortable(false)
        ];
    }
}
