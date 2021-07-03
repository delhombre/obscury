<?php

namespace App\Form;

use App\Entity\Album;
use App\Entity\Musique;
use App\Entity\Category;
use App\Entity\User;
use App\Repository\AlbumRepository;
use App\Repository\UserRepository;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Security\Core\Security;
use Vich\UploaderBundle\Form\Type\VichFileType;
use Symfony\Component\Form\FormBuilderInterface;
use Vich\UploaderBundle\Form\Type\VichImageType;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\FileType;

class MusiqueType extends AbstractType
{
    private $security;

    public function __construct(Security $security)
    {
        $this->security = $security;
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('title')
            ->add('featuring')
            ->add('category', EntityType::class, [
                'class' => Category::class,
                'choice_label' => 'title',
                'placeholder' => 'Sélectionner une catégorie'
            ])
            ->add('imageFile', FileType::class)
            ->add('musicFile', FileType::class);

        $user = $this->security->getUser();
        $builder->addEventListener(FormEvents::PRE_SET_DATA, function (FormEvent $event) use ($user) {

            $form = $event->getForm();
            $formOptions = [
                'class' => Album::class,
                'choice_label' => 'title',
                'query_builder' => function (AlbumRepository $albumRepository) use ($user) {
                    return $albumRepository->createQueryBuilder('a')
                        ->andWhere('a.user = :user')
                        ->setParameter('user', $user->getId());
                },
                'required' => false,
                'placeholder' => 'Vos albums',
            ];
            $form->add('album', EntityType::class, $formOptions);
        });
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => Musique::class,
        ]);
    }
}
