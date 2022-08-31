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
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\UrlType;

class ServicesType extends BaseFormType
{
    /**
     * @param FormBuilderInterface $builder
     * @param array $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {

        $builder
            ->add('title', TextType::class, array('required' => false, 'label' => 'Title ', 'help' => 'Max 100 Characters (Recommended)'))
            ->add($this->getMediaBuilder($builder, 'image', 'Image', true, 'Max Dimensions: 783 x 452 px', array('provider' => 'sonata.media.provider.svg')))
            ->add('content', CKEditorType::class, array('attr' => array('rows' => '3'), 'required' => false, 'label' => 'Content ', 'help' => 'Max 600 Characters (Recommended)'))
            ->add('page', EntityType::class, array(
                'label' => 'Page',
                'choice_label' => 'name',
                'choice_value' => 'id',
                'expanded' => false,
                'placeholder' => 'Select Page',
                'required' => false,
                'class' => Page::class,
                'query_builder' => function (EntityRepository $er) {
                    return $er->createQueryBuilder('s')
                        ->Where('s.enabled = :enabled')
                        ->andWhere('s.site = :site')
                        ->andWhere('s.routeName NOT IN (:routeName)')
                        ->setParameter('routeName', array('_page_internal_error_not_found', '_page_internal_error_fatal', '_page_internal_global'))
                        ->setParameter('enabled', '1')
                        ->setParameter('site', $this->getSite())
                        ->orderBy('s.id', 'ASC');
                }));
    }
}
