<?php

namespace AppBundle\Form;

use AppBundle\Entity\User;
use Doctrine\DBAL\Types\DateType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Doctrine\ORM\EntityRepository;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;

class PedidoType extends AbstractType
{
    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('codigo')
            ->add('fechaRecepcion',\Symfony\Component\Form\Extension\Core\Type\DateType::class, array(
            'widget' => 'single_text',
            'format' => 'yyyy-MM-dd'))
            ->add('fechaEntrega')
            ->add('listaSocios',FileType::class,array(
            "label" => "Imagen:",
            "attr" =>array("class" => "form-control")
        ))->add('user', EntityType::class, array(
            'class' => 'AppBundle:User',
            'query_builder' => function (EntityRepository $er) {
                return $er->createQueryBuilder('u')
                    ->where('u.enabled = true')
                    ->andWhere('u.roles LIKE  :roles')
                    ->setParameter('roles', '%"' . 'ROLE_USER' . '"%')

                    ->orderBy('u.username', 'ASC');
            },
            'choice_label' => 'username',
        ));




    }/**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'AppBundle\Entity\Pedido'
        ));

    }

    /**
     * {@inheritdoc}
     */
    public function getBlockPrefix()
    {
        return 'appbundle_pedido';
    }


}
