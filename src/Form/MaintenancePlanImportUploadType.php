<?php

declare(strict_types=1);

namespace App\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Validator\Constraints\File;
use Symfony\Component\Validator\Constraints\NotBlank;

final class MaintenancePlanImportUploadType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder->add('file', FileType::class, [
            'label' => 'Файл регламенту (.csv або .xlsx)',
            'constraints' => [
                new NotBlank(message: 'Оберіть файл.'),
                new File(
                    maxSize: '8M',
                    mimeTypes: [
                        'text/csv',
                        'text/plain',
                        'application/csv',
                        'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
                        'application/vnd.ms-excel',
                    ],
                    mimeTypesMessage: 'Дозволені лише CSV або XLSX.',
                ),
            ],
        ]);
    }
}
