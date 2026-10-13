<?php

declare(strict_types=1);

namespace App\Form;

use App\Admin\Appointment\ServiceAppointmentDraft;
use App\Entity\MaintenanceProcedure;
use App\Entity\ServiceStation;
use App\Entity\User;
use App\Entity\Vehicle;
use App\Entity\VehicleGeneration;
use App\Repository\MaintenanceProcedureRepository;
use App\Repository\VehicleRepository;
use App\Service\Admin\ServiceStationAccess;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\Count;
use Symfony\Component\Validator\Constraints\NotBlank;

/** @extends AbstractType<ServiceAppointmentDraft> */
final class ServiceAppointmentCreateType extends AbstractType
{
    public function __construct(
        private readonly ServiceStationAccess $stationAccess,
    ) {
    }

    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        /** @var array<string, string> $slotChoices */
        $slotChoices = $options['slot_choices'];
        /** @var User $bookingActor */
        $bookingActor = $options['booking_actor'];
        $accessibleStations = $this->stationAccess->accessibleActiveStations($bookingActor);
        $unrestricted = $this->stationAccess->isUnrestricted($bookingActor);

        $builder
            ->add('station', EntityType::class, [
                'label' => 'appointments.form.station',
                'translation_domain' => 'messages',
                'class' => ServiceStation::class,
                'choices' => $accessibleStations,
                'choice_label' => static function (ServiceStation $station): string {
                    $city = $station->getCityUk();

                    return $city !== null && $city !== ''
                        ? sprintf('%s · %s', $station->getNameUk(), $city)
                        : $station->getNameUk();
                },
                'constraints' => [new NotBlank(message: 'Оберіть СТО.')],
                'attr' => [
                    'data-ea-widget' => 'ea-autocomplete',
                    'class' => 'form-select',
                    'data-appt-field' => 'station',
                ],
            ])
            ->add('vehicle', EntityType::class, [
                'label' => 'appointments.form.vehicle',
                'translation_domain' => 'messages',
                'class' => Vehicle::class,
                'required' => false,
                'choice_label' => static function (Vehicle $vehicle): string {
                    $generation = $vehicle->getGeneration();
                    $model = $generation?->getModel();
                    $brand = $model?->getBrand();
                    $title = trim(sprintf(
                        '%s %s %d',
                        $brand?->getName() ?? '',
                        $model?->getName() ?? '',
                        $vehicle->getYear()
                    ));
                    $plate = $vehicle->getLicensePlate();
                    $owner = $vehicle->getUser()?->getFullName() ?? $vehicle->getUser()?->getEmail();

                    return trim(sprintf('%s · %s · %s', $title, $plate ?? 'без номера', $owner ?? ''));
                },
                'query_builder' => static function (VehicleRepository $repository) use ($bookingActor, $unrestricted, $accessibleStations) {
                    return $repository->createAppointmentBookingQueryBuilder($bookingActor, $unrestricted, $accessibleStations);
                },
                'placeholder' => 'appointments.form.vehicle_placeholder',
                'attr' => [
                    'data-ea-widget' => 'ea-autocomplete',
                    'class' => 'form-select',
                    'data-appt-field' => 'vehicle',
                ],
            ])
            ->add('registerNewVehicle', CheckboxType::class, [
                'label' => 'appointments.form.register_new_vehicle',
                'translation_domain' => 'messages',
                'required' => false,
                'attr' => ['data-appt-toggle' => 'new-vehicle'],
            ])
            ->add('newOwnerName', TextType::class, [
                'label' => 'appointments.form.new_owner_name',
                'translation_domain' => 'messages',
                'required' => false,
                'attr' => ['class' => 'form-control', 'data-appt-new-vehicle' => '1'],
            ])
            ->add('newOwnerEmail', EmailType::class, [
                'label' => 'appointments.form.new_owner_email',
                'translation_domain' => 'messages',
                'required' => false,
                'attr' => ['class' => 'form-control', 'data-appt-new-vehicle' => '1'],
            ])
            ->add('newLicensePlate', TextType::class, [
                'label' => 'appointments.form.new_license_plate',
                'translation_domain' => 'messages',
                'required' => false,
                'attr' => ['class' => 'form-control', 'data-appt-new-vehicle' => '1'],
            ])
            ->add('newYear', IntegerType::class, [
                'label' => 'appointments.form.new_year',
                'translation_domain' => 'messages',
                'required' => false,
                'attr' => ['class' => 'form-control', 'data-appt-new-vehicle' => '1'],
            ])
            ->add('newMileageKm', IntegerType::class, [
                'label' => 'appointments.form.new_mileage',
                'translation_domain' => 'messages',
                'required' => false,
                'attr' => ['class' => 'form-control', 'data-appt-new-vehicle' => '1'],
            ])
            ->add('newGeneration', EntityType::class, [
                'label' => 'appointments.form.new_generation',
                'translation_domain' => 'messages',
                'class' => VehicleGeneration::class,
                'required' => false,
                'choice_label' => static function (VehicleGeneration $generation): string {
                    $model = $generation->getModel();
                    $brand = $model?->getBrand();

                    return trim(sprintf('%s %s %s', $brand?->getName() ?? '', $model?->getName() ?? '', $generation->getName()));
                },
                'placeholder' => 'appointments.form.new_generation_placeholder',
                'attr' => [
                    'data-ea-widget' => 'ea-autocomplete',
                    'class' => 'form-select',
                    'data-appt-new-vehicle' => '1',
                ],
            ])
            ->add('serviceDate', DateType::class, [
                'label' => 'appointments.form.service_date',
                'translation_domain' => 'messages',
                'widget' => 'single_text',
                'input' => 'datetime_immutable',
                'constraints' => [new NotBlank(message: 'Оберіть дату візиту.')],
                'attr' => ['class' => 'form-control', 'data-appt-field' => 'date'],
            ])
            ->add('serviceTime', ChoiceType::class, [
                'label' => 'appointments.form.service_time',
                'translation_domain' => 'messages',
                'choices' => array_flip($slotChoices),
                'placeholder' => 'appointments.form.service_time_placeholder',
                'constraints' => [new NotBlank(message: 'Оберіть час візиту.')],
                'attr' => [
                    'data-ea-widget' => 'ea-autocomplete',
                    'class' => 'form-select',
                    'data-appt-field' => 'time',
                ],
            ])
            ->add('procedures', EntityType::class, [
                'label' => 'appointments.form.procedures',
                'translation_domain' => 'messages',
                'class' => MaintenanceProcedure::class,
                'choice_label' => 'titleUk',
                'multiple' => true,
                'expanded' => false,
                'query_builder' => static function (MaintenanceProcedureRepository $repository) use ($bookingActor, $unrestricted, $accessibleStations, $options) {
                    return $repository->createBookingChoicesQueryBuilder(
                        $options['booking_station'],
                        $bookingActor,
                        $unrestricted,
                        $accessibleStations
                    );
                },
                'constraints' => [
                    new Count(min: 1, minMessage: 'Оберіть хоча б одну роботу.'),
                ],
                'attr' => [
                    'data-ea-widget' => 'ea-autocomplete',
                    'class' => 'form-select',
                    'data-appt-field' => 'procedures',
                ],
            ])
            ->add('estimatedCostUah', IntegerType::class, [
                'label' => 'appointments.form.estimated_cost',
                'translation_domain' => 'messages',
                'required' => false,
                'help' => 'appointments.form.estimated_cost_help',
                'attr' => ['class' => 'form-control', 'min' => 0, 'step' => 1],
            ])
            ->add('notesUk', TextareaType::class, [
                'label' => 'appointments.form.notes_uk',
                'translation_domain' => 'messages',
                'required' => false,
                'attr' => ['rows' => 3, 'class' => 'form-control'],
            ]);

        $builder->addEventListener(FormEvents::PRE_SUBMIT, static function (FormEvent $event): void {
            $data = $event->getData();

            if (!is_array($data)) {
                return;
            }

            if (!empty($data['registerNewVehicle'])) {
                $data['vehicle'] = null;
                $event->setData($data);
            }
        });
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => ServiceAppointmentDraft::class,
            'slot_choices' => [],
            'booking_actor' => null,
            'booking_station' => null,
        ]);

        $resolver->setAllowedTypes('slot_choices', 'array');
        $resolver->setAllowedTypes('booking_actor', User::class);
        $resolver->setAllowedTypes('booking_station', ['null', ServiceStation::class]);
        $resolver->setRequired(['booking_actor']);
    }
}
