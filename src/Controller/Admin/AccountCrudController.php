<?php

namespace App\Controller\Admin;

use App\Entity\Account;
use EasyCorp\Bundle\EasyAdminBundle\Config\Action;
use EasyCorp\Bundle\EasyAdminBundle\Config\Actions;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\AssociationField;
use EasyCorp\Bundle\EasyAdminBundle\Field\ChoiceField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;

class AccountCrudController extends AbstractCrudController
{
    public static function getEntityFqcn(): string
    {
        return Account::class;
    }

    public function configureActions(Actions $actions): Actions
    {
        return $actions->disable(Action::NEW);
    }

    public function configureCrud(Crud $crud): Crud
    {
        return $crud
            ->setDefaultSort(['id' => 'DESC'])
            ->setEntityPermission('ROLE_SUPER_ADMIN')
        ;
    }

    public function configureFields(string $pageName): iterable
    {
        return [
            TextField::new('username'),
            ChoiceField::new('roles')
                ->setChoices([
                    'SUPER_ADMIN' => 'ROLE_SUPER_ADMIN',
                    'ADMIN' => 'ROLE_ADMIN'
                ])
                ->allowMultipleChoices()
                ->renderExpanded(),
            AssociationField::new('postComments')->hideOnForm(),
            AssociationField::new('files')->hideOnForm(),
        ];
    }
}
