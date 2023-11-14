<?php

namespace App\Form;

use App\Entity\File;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\File as FileValidator;
use Symfony\UX\Dropzone\Form\DropzoneType;

class FileFormType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('file', DropzoneType::class, [
                'label' => false,

                // unmapped means that this field is not associated to any entity property
                'mapped' => false,

                // unmapped fields can't define their validation using annotations
                // in the associated entity, so you can use the PHP constraint classes
                'constraints' => [
                    new FileValidator([
//                        'maxSize' => '500M',
                        'mimeTypes' => [
                            'image/*',
                            'audio/*',
                            'video/*',
                            'application/pdf',
//                            'application/msword', // DOC
//                            'application/vnd.ms-excel', // XLS
//                            'application/vnd.ms-powerpoint', // PPT
//                            'application/vnd.openxmlformats-officedocument.wordprocessingml.document', // DOCX
//                            'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet', // XLSX
//                            'application/vnd.openxmlformats-officedocument.presentationml.presentation', // PPTX
                            'text/plain'
                        ],
                        'mimeTypesMessage' => 'Please upload a valid format.',
                    ])
                ],
            ])
            ->add('private')
            ->add('submit', SubmitType::class, [
                'row_attr' => ['class' => 'mb-0']
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => File::class,
        ]);
    }
}
