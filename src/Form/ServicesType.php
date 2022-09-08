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
        $page = $this->getPage();
        $help = 'Max Dimensions: 783 x 452 px';
        $provider = 'sonata.media.provider.image';
        if ($this->getBlockType() == 'sonata.cms.block.services') {
            $help = 'Max Dimensions: 30 x 30 px';
            $provider = 'sonata.media.provider.svg';
        }
        $builder
            ->add('title', TextType::class, array('required' => false, 'label' => 'Title ', 'help' => 'Max 100 Characters (Recommended)'));
        if ($this->getBlockType() == 'sonata.cms.block.home_services') {
            $builder->add($this->getMediaBuilder($builder, 'background_image', 'Background Image', true, 'Max Dimensions: 1536 x 372 px', array('provider' => 'sonata.media.provider.image')));
        }
        $builder->add($this->getMediaBuilder($builder, 'image', 'Image', true, $help, array('provider' => $provider)))
            ->add('content', CKEditorType::class, array('attr' => array('rows' => '3'), 'required' => false, 'label' => 'Content ', 'help' => 'Max 600 Characters (Recommended)'))
            ->add('page', EntityType::class, array(
                'label' => 'Page',
                'choice_label' => 'name',
                'choice_value' => 'id',
                'expanded' => false,
                'placeholder' => 'Select Page',
                'required' => false,
                'class' => Page::class,
                'query_builder' => function (EntityRepository $er) use ($page) {
//                    $parent = $page->getId();
//                    if ($this->getPageTemplate() == 'home') {
//                        $parent = $this->getServicesPage()->getId();
//                    }
                    return $er->createQueryBuilder('s')
                        ->Where('s.enabled = :enabled')
                        ->andWhere('s.site = :site')
                        ->andWhere('s.routeName NOT IN (:routeName)')
                        //->andWhere('s.parent = :parent')
                        ->setParameter('routeName', array('_page_internal_error_not_found', '_page_internal_error_fatal', '_page_internal_global'))
                        ->setParameter('enabled', '1')
                        ->setParameter('site', $this->getSite())
                        //->setParameter('parent', $parent)
                        ->orderBy('s.id', 'ASC');
                }));
    }
}
