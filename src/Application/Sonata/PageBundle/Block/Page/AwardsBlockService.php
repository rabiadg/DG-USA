<?php

namespace App\Application\Sonata\PageBundle\Block\Page;

use App\Application\Sonata\MediaBundle\Entity\Media;
use App\Application\Sonata\PageBundle\Block\BaseBlockService;
use App\Form\FAQType;
use App\Form\ImageLinkType;
use App\Form\ImageType;
use App\Form\ServicesType;
use App\Form\SocialMediaLinkType;
use FOS\CKEditorBundle\Form\Type\CKEditorType;
use Sonata\BlockBundle\Block\BlockContextInterface;
use Sonata\BlockBundle\Model\BlockInterface;
use Sonata\AdminBundle\Form\Type\CollectionType;
use Sonata\Form\Type\ImmutableArrayType;
use Sonata\BlockBundle\Meta\Metadata;
use Sonata\Form\Validator\ErrorElement;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\UrlType;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\Validator\Constraints\NotBlank;
use Twig\Environment;
use Sonata\BlockBundle\Form\Mapper\FormMapper;

class AwardsBlockService extends BaseBlockService
{
    protected $container;
    protected $manager;

    /**
     * @var Environment
     */
    protected $twig;

    public function __construct(Environment $twig, ContainerInterface $container)
    {
        $this->container = $container;
        $this->manager = $this->container->get('doctrine')->getManager();

        parent::__construct($twig, $container);
    }

    public function getName()
    {
        return 'Award Section';
    }

    public function configureSettings(OptionsResolver $resolver): void
    {
        $resolver->setDefaults(array(
            'title' => false,
            'sub_title' => false,
            'description' => false,
            'awards' => null,
            'template' => 'ApplicationSonataPageBundle::Block/Page/awards_section.html.twig',



        ));
    }

    public function execute(BlockContextInterface $blockContext, Response $response = null): Response
    {
        return $this->renderResponse($blockContext->getTemplate(), array(
            //'context' => $blockContext,
            'block' => $blockContext->getBlock(),
            'settings' => $blockContext->getSettings(),
        ), $response);
    }

    public function configureEditForm(FormMapper $formMapper, BlockInterface $block): void
    {
        //$this->container->
        //$mediaAdmin = $this->configurationPool()->getAdminByClass("Application\Sonata\MediaBundle\Entity\Media");
        $formMapper
            ->add('settings', ImmutableArrayType::class, array(
                'keys' => array(
                    array('title', TextType::class, array('required' => false, 'label' => 'Title ', 'help' => 'Max 50 Characters (Recommended)')),
                    array('sub_title', TextType::class, array('required' => false, 'label' => 'Sub Title ', 'help' => 'Max 120 Characters (Recommended)')),
                    array('description', TextareaType::class, array('attr' => array('rows' => '3'), 'required' => false, 'label' => 'Description ', 'help' => 'Max 120 Characters (Recommended)')),
                    array('awards', CollectionType::class,
                        array(
                            'required' => false,
                            'allow_add' => true,
                            'allow_delete' => true,
                            'prototype' => true,
                            'by_reference' => false,
                            'allow_extra_fields' => true,
                            'entry_type' => ImageLinkType::class,

                        )),
                )
            ));
    }

    public function configureCreateForm(FormMapper $formMapper, BlockInterface $block): void
    {
        //$mediaAdmin = $this->configurationPool()->getAdminByClass("Application\\Sonata\\MediaBundle\\Entity\\Media");
        $formMapper
            ->add('settings', ImmutableArrayType::class, array(
                'keys' => array(
                    array('title', TextType::class, array('required' => false, 'label' => 'Title ', 'help' => 'Max 50 Characters (Recommended)')),
                    array('sub_title', TextType::class, array('required' => false, 'label' => 'Sub Title ', 'help' => 'Max 600 Characters (Recommended)')),
                    array('description', TextareaType::class, array('attr' => array('rows' => '3'), 'required' => false, 'label' => 'Description ', 'help' => 'Max 120 Characters (Recommended)')),
                    array('awards', CollectionType::class,
                        array(
                            'required' => false,
                            'allow_add' => true,
                            'allow_delete' => true,
                            'prototype' => true,
                            'by_reference' => false,
                            'allow_extra_fields' => true,
                            'entry_type' => ImageLinkType::class,

                        )),
                )
            ));
    }


    public function validate(ErrorElement $errorElement, BlockInterface $block): void
    {
        $awards = $block->getSetting('awards', null);
        $count = 0;
        foreach ($awards as $award) {

            if ($award['image'] && !in_array($award['image']->getContentType(), ['image/jpg', 'image/jpeg', 'image/png', 'image/x-png'])) {
                $errorElement
                    ->with('settings[awards]['.$count.'][image]')
                    ->addViolation('Invalid file type only jpeg,jpg,png allowed')
                    ->end();
            };
            $count ++;
        }
               /*$errorElement
                   ->with('settings[title]')
                   ->assertNotBlank()
                   ->end();*/


    }

    public function getBlockMetadata($code = null)
    {
        return new Metadata($this->getName(), (null !== $code ? $code : $this->getName()), false, 'SonataBlockBundle', [
            'class' => 'fa fa-file-text-o',
        ]);
    }


    public function load(BlockInterface $block): void
    {
        $awards = array();
        if ($block->getSetting('awards') != null and count($block->getSetting('awards')) > 0) {
            $count = 0;
            foreach ($block->getSetting('brands') as $award) {
                $media = (isset($award['image'])) ? $award['image'] : null;
                if (is_int($award['image'])) {
                    $media = $this->mediaManager->findOneBy(array('id' => $award['image']));
                }
                $awards[$count]['link'] = ($award['link']) ? $award['link'] : null;
                $awards[$count]['image'] = (is_object($media)) ? $media : null;
                $count++;
            }
        }
        $block->setSetting('brands', $awards);

    }

}
