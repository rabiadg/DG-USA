<?php

namespace App\Application\Sonata\PageBundle\Block\Page\Home;

use App\Application\Sonata\MediaBundle\Entity\Media;
use App\Application\Sonata\PageBundle\Block\BaseBlockService;
use App\Form\CaseStudyType;
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

class OurWorkBlockService extends BaseBlockService
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
        return 'Our Work Section';
    }

    public function configureSettings(OptionsResolver $resolver): void
    {
        $resolver->setDefaults(array(
            'title' => false,
            'case_studies' => null,
            'template' => 'Application/Sonata/PageBundle/Resources/views/Block/Page/Home/our_work_section.html.twig',


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
                    array('case_studies', CollectionType::class,
                        array(
                            'required' => false,
                            'allow_add' => true,
                            'allow_delete' => true,
                            'prototype' => true,
                            'by_reference' => false,
                            'allow_extra_fields' => true,
                            'entry_type' => CaseStudyType::class,

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
                    array('case_studies', CollectionType::class,
                        array(
                            'required' => false,
                            'allow_add' => true,
                            'allow_delete' => true,
                            'prototype' => true,
                            'by_reference' => false,
                            'allow_extra_fields' => true,
                            'entry_type' => CaseStudyType::class,

                        )),
                )
            ));
    }


    public function validate(ErrorElement $errorElement, BlockInterface $block): void
    {

    }

    public function getBlockMetadata($code = null)
    {
        return new Metadata($this->getName(), (null !== $code ? $code : $this->getName()), false, 'SonataBlockBundle', [
            'class' => 'fa fa-file-text-o',
        ]);
    }


    public function load(BlockInterface $block): void
    {
        $caseStudies = array();
        if ($block->getSetting('case_studies') != null and count($block->getSetting('case_studies')) > 0) {
            $count = 0;
            foreach ($block->getSetting('case_studies') as $case) {
                $image = (isset($case['image'])) ? $case['image'] : null;
                if (is_int($case['image'])) {
                    $image = $this->mediaManager->findOneBy(array('id' => $case['image']));
                }
                $logo = (isset($case['logo'])) ? $case['logo'] : null;
                if (is_int($case['logo'])) {
                    $logo = $this->mediaManager->findOneBy(array('id' => $case['logo']));
                }
                $page = (isset($case['page'])) ? $case['page'] : null;
                if (is_int($case['page'])) {
                    $page = $this->getPageById($case['page']);
                }
                $caseStudies[$count]['image'] = (is_object($image)) ? $image : null;
                $caseStudies[$count]['logo'] = (is_object($logo)) ? $logo : null;
                $caseStudies[$count]['description'] = ($case['description']) ? $case['description'] : null;
                $caseStudies[$count]['page'] = (is_object($page)) ? $page : null;
                $count++;
            }
        }
        $block->setSetting('case_studies', $caseStudies);
    }

}
