<?php

namespace App\Form;

use App\Application\Sonata\PageBundle\Entity\Page;
use App\Entity\FrontUser;
use Doctrine\ORM\EntityRepository;
use FOS\CKEditorBundle\Form\Type\CKEditorType;
use Sonata\AdminBundle\Form\Type\ModelListType;
use Sonata\PageBundle\Model\SiteManagerInterface;
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

class CaseStudyType extends BaseFormType
{


    /**
     * @param FormBuilderInterface $builder
     * @param array $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {

        $builder
            ->add($this->getMediaBuilder($builder, 'image', 'Image', true, 'Max Dimensions: 291 x 323 px', array('provider' => 'sonata.media.provider.image')))
            ->add($this->getMediaBuilder($builder, 'logo', 'Logo', true, 'Max Dimensions: 204 x 64 px', array('provider' => 'sonata.media.provider.svg')))
            ->add('description', TextareaType::class, array('attr' => array('rows' => '3'), 'required' => false, 'label' => 'Description ', 'help' => 'Max 200 Characters (Recommended)'))
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
