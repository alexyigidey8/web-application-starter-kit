<?php

namespace Application\Form\Type\User;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;
use Application\Form\Type\ProfileType;

/**
 * @author Borut Balažek <bobalazek124@gmail.com>
 */
class SettingsType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->add(
            'profile',
            new ProfileType()
        );

        $builder->add('username', 'text', array(
            'label' => 'Username',
        ));
        $builder->add('email', 'email', array(
            'read_only' => true,
        ));

        $builder->add('submitButton', 'submit', array(
            'label' => 'Submit',
            'attr' => array(
                'class' => 'btn-primary btn-lg btn-block',
            ),
        ));
    }

    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'Application\Entity\UserEntity',
            'validation_groups' => array('settings'),
            'csrf_protection' => true,
            'csrf_field_name' => 'csrf_token',
            'cascade_validation' => true,
        ));
    }

    public function getName()
    {
        return 'user';
    }
}
