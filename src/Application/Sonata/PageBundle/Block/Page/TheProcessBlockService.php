<?php

namespace App\Application\Sonata\PageBundle\Block\Page;

use App\Application\Sonata\MediaBundle\Entity\Media;
use App\Application\Sonata\PageBundle\Block\BaseBlockService;
use App\Form\PortfolioType;
use App\Form\ProcessType;
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

class TheProcessBlockService extends BaseBlockService
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
        return 'The Process Section';
    }

    public function configureSettings(OptionsResolver $resolver): void
    {
        $resolver->setDefaults(array(
            'title' => false,
            'description' => false,
            'processCards' => null,
            'template' => 'Application/Sonata/PageBundle/Resources/views/Block/Page/process_section.html.twig',


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
                    array('description', TextareaType::class, array('attr' => array('rows' => '3'), 'required' => false, 'label' => 'Description ', 'help' => 'Max 200 Characters (Recommended)')),
                    array('processCards', CollectionType::class,
                        array(
                            'required' => false,
                            'allow_add' => true,
                            'allow_delete' => true,
                            'prototype' => true,
                            'by_reference' => false,
                            'allow_extra_fields' => true,
                            'entry_type' => ProcessType::class,

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
                    array('description', TextareaType::class, array('attr' => array('rows' => '3'), 'required' => false, 'label' => 'Description ', 'help' => 'Max 200 Characters (Recommended)')),
                    array('processCards', CollectionType::class,
                        array(
                            'required' => false,
                            'allow_add' => true,
                            'allow_delete' => true,
                            'prototype' => true,
                            'by_reference' => false,
                            'allow_extra_fields' => true,
                            'entry_type' => ProcessType::class,

                        )),
                )
            ));
    }


    public function validate(ErrorElement $errorElement, BlockInterface $block): void
    {
        $processCards = $block->getSetting('processCards', null);

        if (isset($processCards) and count($processCards) > 4) {
            $errorElement
                ->with('settings[processCards]')
                ->addViolation('Only 4 Processes Allowed')
                ->end();
        };
    }

    public function getBlockMetadata($code = null)
    {
        return new Metadata($this->getName(), (null !== $code ? $code : $this->getName()), false, 'SonataBlockBundle', [
            'class' => 'fa fa-file-text-o',
        ]);
    }


    public function load(BlockInterface $block): void
    {

        $processCards = array();
        if ($block->getSetting('processCards') != null and count($block->getSetting('processCards')) > 0) {
            $count = 0;
            foreach ($block->getSetting('processCards') as $item) {
                $icon_image = (isset($item['icon'])) ? $item['icon'] : null;
                if (is_int($item['icon'])) {
                    $icon_image = $this->mediaManager->findOneBy(array('id' => $item['icon']));
                }

                $processCards[$count]['icon'] = (is_object($icon_image)) ? $icon_image : null;
                $processCards[$count]['title'] = ($item['title']) ? $item['title'] : null;
                $processCards[$count]['content'] = ($item['content']) ? $item['content'] : null;
                $count++;
            }
        }
        $block->setSetting('processCards', $processCards);
    }

}
