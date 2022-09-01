<?php

namespace App\Form;

use App\Application\Sonata\PageBundle\Entity\Page;
use App\Entity\FrontUser;
use Doctrine\ORM\EntityRepository;
use FOS\CKEditorBundle\Form\Type\CKEditorType;
use Sonata\AdminBundle\Form\Type\ModelListType;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\UrlType;

class ProcessType extends BaseFormType
{
    /**
     * @param FormBuilderInterface $builder
     * @param array $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add($this->getMediaBuilder($builder, 'icon', 'Icon', true, 'Max Dimensions: 50 x 50 px', array('provider' => 'sonata.media.provider.image')))
            ->add('title', TextType::class, array('required' => false, 'label' => 'Title ', 'help' => 'Max 100 Characters (Recommended)'))
            ->add('content', CKEditorType::class, array('attr' => array('rows' => '3'), 'required' => false, 'label' => 'Content '));
    }
}
