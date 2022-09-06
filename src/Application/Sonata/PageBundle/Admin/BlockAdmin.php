<?php

declare(strict_types=1);

/*
 * This file is part of the Sonata Project package.
 *
 * (c) Thomas Rabaix <thomas.rabaix@sonata-project.org>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace App\Application\Sonata\PageBundle\Admin;

use Doctrine\ORM\EntityRepository;
use Sonata\AdminBundle\Form\FormMapper;
use Sonata\AdminBundle\Route\RouteCollectionInterface;
use Sonata\AdminBundle\Security\Acl\Permission\AdminPermissionMap;
use Sonata\BlockBundle\Block\BlockServiceManagerInterface;
use Sonata\BlockBundle\Block\Service\BlockServiceInterface;
use Sonata\BlockBundle\Block\Service\EditableBlockService;
use Sonata\BlockBundle\Form\Type\ServiceListType;
use Sonata\BlockBundle\Model\BlockInterface;
use Sonata\MediaBundle\Model\MediaInterface;
use Sonata\PageBundle\Admin\BaseBlockAdmin;
use Sonata\PageBundle\Model\PageBlockInterface;
use Sonata\PageBundle\Model\PageInterface;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * @author Thomas Rabaix <thomas.rabaix@sonata-project.org>
 */
final class BlockAdmin extends BaseBlockAdmin
{
    protected $classnameLabel = 'Block';

    /**
     * @var array<string, array{
     *   templates?: array<array{
     *     name: string,
     *     template: string,
     *   }>,
     * }>
     */
private array $blocks;

    /**
     * @param array<string, array{
     *   templates?: array<array{
     *     name: string,
     *     template: string,
     *   }>,
     * }> $blocks
     */
    public function __construct(BlockServiceManagerInterface $blockManager, array $blocks = [])
    {
        parent::__construct($blockManager);

        $this->blocks = $blocks;
    }

    protected function getAccessMapping(): array
    {
        return [
            'savePosition' => AdminPermissionMap::PERMISSION_EDIT,
            'switchParent' => AdminPermissionMap::PERMISSION_EDIT,
            'composePreview' => AdminPermissionMap::PERMISSION_EDIT,
        ];
    }

    protected function configurePersistentParameters(): array
    {
        $parameters = parent::configurePersistentParameters();

        if (!$this->hasRequest()) {
            return $parameters;
        }

        $composer = $this->getRequest()->get('composer');

        if (null !== $composer) {
            $parameters['composer'] = $composer;
        }

        return $parameters;
    }

    protected function configureRoutes(RouteCollectionInterface $collection): void
    {
        parent::configureRoutes($collection);

        $collection->add('save_position', 'save-position');
        $collection->add('switch_parent', 'switch-parent');
        $collection->add('compose_preview', $this->getRouterIdParameter() . '/compose-preview');
    }

    protected function configureFormFields(FormMapper $form): void
    {
        $block = $this->hasSubject() ? $this->getSubject() : null;

        if (null === $block) { // require to have a proper running test suite at the sandbox level
            return;
        }

        $page = null;

        if ($this->isChild()) {
            $page = $this->getParent()->getSubject();

            if (!$page instanceof PageInterface) {
                throw new \RuntimeException('The BlockAdmin must be attached to a parent PageAdmin');
            }

            if ($this->hasRequest() && null === $block->getId()) { // new block
                $block->setType($this->getRequest()->get('type'));
                $block->setPage($page);
            }

            $blockPage = $block->getPage();

            if (null === $blockPage || $blockPage->getId() !== $page->getId()) {
                throw new \RuntimeException('The page reference on BlockAdmin and parent admin are not the same');
            }
        }

        $blockType = $block->getType();

        $isComposer = $this->hasRequest() ? $this->getRequest()->get('composer', false) : false;
        $generalGroupOptions = $optionsGroupOptions = [];
        if (false !== $isComposer) {
            $generalGroupOptions['class'] = 'hidden';
            $optionsGroupOptions['name'] = '';
        }

        $form->with('general', $generalGroupOptions);

        if (false !== $isComposer) {
            $form->add('name', HiddenType::class);
        } else {
            $form->add('name');
        }

        $form->end();

        $isContainerRoot = \in_array($blockType, ['sonata.page.block.container', 'sonata.block.service.container'], true) && !$this->hasParentFieldDescription();
        $isStandardBlock = !\in_array($blockType, ['sonata.page.block.container', 'sonata.block.service.container'], true) && !$this->hasParentFieldDescription();

        if ($isContainerRoot || $isStandardBlock) {
            $form->with('general', $generalGroupOptions);

            $containerBlockTypes = $this->containerBlockTypes;

            // need to investigate on this case where $page == null ... this should not be possible
            if ($isStandardBlock && null !== $page && [] !== $containerBlockTypes) {
                $form->add('parent', EntityType::class, [
                    'class' => $this->getClass(),
                    'query_builder' => static fn(EntityRepository $repository) => $repository->createQueryBuilder('a')
                    ->andWhere('a.page = :page AND a.type IN (:types)')
                    ->setParameters([
                        'page' => $page,
                        'types' => $containerBlockTypes,
                    ]),
                ], [
                    'admin_code' => $this->getCode(),
                ]);
            }

            if ($isStandardBlock) {
                $form->add('position', IntegerType::class);
            }

            $form->end();

            $form->with('options', $optionsGroupOptions);

            $this->configureBlockFields($form, $block);

            if (false !== $isComposer) {
                $form->add('enabled', HiddenType::class, ['data' => true]);
            } else {
                $form->add('enabled');
            }

            $form->end();
        } else {
            $form
                ->with('options', $optionsGroupOptions)
                ->add('type', ServiceListType::class, ['context' => 'sonata_page_bundle'])
                ->add('enabled')
                ->add('position', IntegerType::class)
                ->end();
        }
    }

    private function getDefaultTemplate(BlockServiceInterface $blockService): ?string
    {
        $resolver = new OptionsResolver();
        $blockService->configureSettings($resolver);
        $options = $resolver->resolve();

        return $options['template'] ?? null;
    }

    /**
     * @param FormMapper<PageBlockInterface> $form
     */
    private function configureBlockFields(FormMapper $form, BlockInterface $block): void
    {
        $blockType = $block->getType();

        if (null === $blockType || !$this->blockManager->has($blockType)) {
            return;
        }

        $service = $this->blockManager->get($block);

        if (!$service instanceof EditableBlockService) {
            throw new \RuntimeException(sprintf(
                'The block "%s" must implement %s',
                $blockType,
                EditableBlockService::class
            ));
        }

        if ($block->getId() > 0) {
            $service->configureEditForm($form, $block);
        } else {
            $service->configureCreateForm($form, $block);
        }

        if ($form->has('settings') && isset($this->blocks[$blockType]['templates'])) {
            $settingsField = $form->get('settings');

            if (!$settingsField->has('template')) {
                $choices = [];

                if (null !== $defaultTemplate = $this->getDefaultTemplate($service)) {
                    $choices['default'] = $defaultTemplate;
                }

                foreach ($this->blocks[$blockType]['templates'] as $item) {
                    $choices[$item['name']] = $item['template'];
                }

                if (\count($choices) > 1) {
                    $templateOptions = [
                        'choices' => $choices,
                    ];

                    $settingsField->add('template', ChoiceType::class, $templateOptions);
                }
            }
        }
    }

    public function prePersist(object $object): void
    {
        $this->uploadBlockImages($object);
        parent::prePersist($object); // TODO: Change the autogenerated stub

    }

    public function preUpdate(object $object): void
    {
        $this->uploadBlockImages($object);
        parent::preUpdate($object); // TODO: Change the autogenerated stub
    }

    public function uploadBlockImages($object)
    {
        if ($object->getType() == 'sonata.cms.block.home_banner') {
            $object->setSetting('banner_image', $object->getSetting('banner_image') instanceof MediaInterface ? $object->getSetting('banner_image')->getId() : null);
            $object->setSetting('banner_right_sec_image', $object->getSetting('banner_right_sec_image') instanceof MediaInterface ? $object->getSetting('banner_right_sec_image')->getId() : null);
            $social_icons = array();
            if ($object->getSetting('social_icons') != null and count($object->getSetting('social_icons')) > 0) {
                $count = 0;
                foreach ($object->getSetting('social_icons') as $social_icon) {
                    $social_icons[$count]['image'] = is_object($social_icon['image']) ? $social_icon['image']->getId() : null;
                    $social_icons[$count]['link'] = ($social_icon['link']) ? $social_icon['link'] : null;
                    $count++;
                }
            }
            $object->setSetting('social_icons', $social_icons);
        } elseif ($object->getType() == 'sonata.cms.block.home_services') {
            $services = array();
            if ($object->getSetting('services') != null and count($object->getSetting('services')) > 0) {
                $count = 0;
                foreach ($object->getSetting('services') as $service) {
                    $services[$count]['title'] = ($service['title']) ? $service['title'] : null;
                    $services[$count]['image'] = (is_object($service['image'])) ? $service['image']->getId() : null;
                    $services[$count]['content'] = ($service['content']) ? $service['content'] : null;
                    $services[$count]['page'] = (is_object($service['page'])) ? $service['page']->getId() : null;
                    $count++;
                }
            }
            $object->setSetting('services', $services);
        } elseif ($object->getType() == 'sonata.cms.block.brands') {
            $brands = array();
            $block = $object;
            if ($block->getSetting('brands') != null and count($block->getSetting('brands')) > 0) {
                $count = 0;
                foreach ($block->getSetting('brands') as $brand) {
                    $brands[$count]['image'] = (is_object($brand['image'])) ? $brand['image']->getId() : null;
                    $count++;
                }
            }
            $block->setSetting('brands', $brands);
        } elseif ($object->getType() == 'sonata.cms.block.awards') {
            $awards = array();
            $block = $object;
            if ($block->getSetting('awards') != null and count($block->getSetting('awards')) > 0) {
                $count = 0;
                foreach ($block->getSetting('awards') as $award) {
                    $awards[$count]['image'] = (is_object($award['image'])) ? $award['image']->getId() : null;
                    $awards[$count]['link'] = ($award['link']) ? $award['link'] : null;
                    $count++;
                }
            }
            $block->setSetting('awards', $awards);
        } elseif ($object->getType() == 'sonata.cms.block.our_work') {
            $block = $object;
            $caseStudies = array();
            if ($block->getSetting('case_studies') != null and count($block->getSetting('case_studies')) > 0) {
                $count = 0;
                foreach ($block->getSetting('case_studies') as $case) {
                    $caseStudies[$count]['image'] = (is_object($case['image'])) ? $case['image']->getId() : null;
                    $caseStudies[$count]['logo'] = (is_object($case['logo'])) ? $case['logo']->getId() : null;
                    $caseStudies[$count]['description'] = ($case['description']) ? $case['description'] : null;
                    $caseStudies[$count]['page'] = (is_object($case['page'])) ? $case['page']->getId() : null;
                    $count++;
                }
            }
            $block->setSetting('case_studies', $caseStudies);
        } elseif ($object->getType() == 'sonata.cms.block.banner_section') {
            $block = $object;
            $banner_image = $block->getSetting('banner_image', null);
            $banner_image = (is_object($banner_image)) ? $banner_image->getId() : null;
            $block->setSetting('banner_image', $banner_image);
        } elseif ($object->getType() == 'sonata.cms.block.portfolio') {
            $block = $object;
            $portFolio = array();
            if ($block->getSetting('portfolioItems') != null and count($block->getSetting('portfolioItems')) > 0) {
                $count = 0;
                foreach ($block->getSetting('portfolioItems') as $item) {
                    $portFolio[$count]['icon_image'] = (is_object($item['icon_image'])) ? $item['icon_image']->getId() : null;
                    $portFolio[$count]['title'] = ($item['title']) ? $item['title'] : null;
                    $portFolio[$count]['sub_title'] = ($item['sub_title']) ? $item['sub_title'] : null;
                    $portFolio[$count]['image'] = (is_object($item['image'])) ? $item['image']->getId() : null;
                    $portFolio[$count]['content'] = ($item['content']) ? $item['content'] : null;
                    $count++;
                }
            }
            $block->setSetting('portfolioItems', $portFolio);
        } elseif ($object->getType() == 'sonata.cms.block.services') {

            $block = $object;
            $banner_image = $block->getSetting('background_image', null);
            $banner_image = (is_object($banner_image)) ? $banner_image->getId() : null;
            $block->setSetting('background_image', $banner_image);

            $services = array();
            if ($object->getSetting('services') != null and count($object->getSetting('services')) > 0) {
                $count = 0;
                foreach ($object->getSetting('services') as $service) {
                    $services[$count]['title'] = ($service['title']) ? $service['title'] : null;
                    $services[$count]['image'] = (is_object($service['image'])) ? $service['image']->getId() : null;
                    $services[$count]['content'] = ($service['content']) ? $service['content'] : null;
                    $services[$count]['page'] = (is_object($service['page'])) ? $service['page']->getId() : null;
                    $count++;
                }
            }
            $object->setSetting('services', $services);
        } elseif ($object->getType() == 'sonata.cms.block.technology_stack') {
            $block = $object;
            $technologies = array();
            if ($block->getSetting('technologies') != null and count($block->getSetting('technologies')) > 0) {
                $count = 0;
                foreach ($block->getSetting('technologies') as $techno) {

                    $technologies[$count]['heading'] = ($techno['heading']) ? $techno['heading'] : null;
                    $technologies[$count]['items']=array();
                    if (isset($techno['items']) and count($techno['items']) > 0) {
                        $i = 0;
                        foreach ($techno['items'] as $item) {
                            $technologies[$count]['items'][$i]['title'] = ($item['title']) ? $item['title'] : null;
                            $technologies[$count]['items'][$i]['icon'] = (is_object($item['icon'])) ? $item['icon']->getId() : null;
                            $i++;
                        }

                    }
                    $count++;
                }
            }
            $block->setSetting('technologies', $technologies);
        } elseif ($object->getType() == '') {
            $block = $object;
            $industries = array();
            if ($block->getSetting('industries') != null and count($block->getSetting('industries')) > 0) {
                $count = 0;
                foreach ($block->getSetting('industries') as $industry) {
                    $industries[$count]['title'] = ($industry['title']) ? $industry['title'] : null;
                    $industries[$count]['image'] = (is_object($industry['image'])) ? $industry['image']->getId() : null;
                    $industries[$count]['content'] = ($industry['content']) ? $industry['content'] : null;
                    $count++;
                }
            }
            $block->setSetting('industries', $industries);
        } elseif ($object->getType() == 'sonata.cms.block.processes') {
            $block=$object;
            $processCards = array();
            if ($block->getSetting('processCards') != null and count($block->getSetting('processCards')) > 0) {
                $count = 0;
                foreach ($block->getSetting('processCards') as $item) {
                    $processCards[$count]['icon'] = (is_object($item['icon'])) ? $item['icon']->getId() : null;
                    $processCards[$count]['title'] = ($item['title']) ? $item['title'] : null;
                    $processCards[$count]['content'] = ($item['content']) ? $item['content'] : null;
                    $count++;
                }
            }
            $block->setSetting('processCards', $processCards);
        }
    }
}
