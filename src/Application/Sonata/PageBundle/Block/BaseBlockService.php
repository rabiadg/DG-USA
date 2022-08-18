<?php


namespace App\Application\Sonata\PageBundle\Block;
use Doctrine\ORM\Mapping\ClassMetadataInfo;
use Sonata\AdminBundle\Form\FormMapper;
use Sonata\AdminBundle\Form\Type\ModelListType;
use Sonata\BlockBundle\Block\Service\AbstractBlockService;
use Sonata\CoreBundle\Model\ManagerInterface;
use Sonata\MediaBundle\Admin\BaseMediaAdmin;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\Templating\EngineInterface;

class BaseBlockService extends AbstractBlockService
{
    protected $container;
    protected $manager;
    /**
     * @var ManagerInterface
     */
    protected $mediaManager;
    /**
     * @var BaseMediaAdmin
     */
    protected $mediaAdmin;

    public function __construct($name, EngineInterface $templating, ContainerInterface $container)
    {
        $this->container = $container;
        $this->manager = $this->container->get('doctrine')->getManager();
        $this->mediaManager = $this->container->get('sonata.media.manager.media');

        parent::__construct($name, $templating, $container);
    }


    protected function getMediaBuilder(FormMapper $formMapper, $name = 'mediaId', $label = 'form.label_media', $required = true, $sonata_help = null,$link_parameters=null)
    {
        // simulate an association ...
        $fieldDescription = $this->getMediaAdmin()->getModelManager()->getNewFieldDescriptionInstance($this->mediaAdmin->getClass(), 'media', [
                'translation_domain' => 'SonataMediaBundle',
                'link_parameters' => $link_parameters
        ]);
        $fieldDescription->setAssociationAdmin($this->getMediaAdmin());
        $fieldDescription->setAdmin($formMapper->getAdmin());
        $fieldDescription->setOption('edit', 'list');
        $fieldDescription->setAssociationMapping([
                'fieldName' => 'media',
                'type' => ClassMetadataInfo::MANY_TO_ONE,
        ]);

        return $formMapper->create($name, ModelListType::class, [
                'sonata_field_description' => $fieldDescription,
                'class' => $this->getMediaAdmin()->getClass(),
                'model_manager' => $this->getMediaAdmin()->getModelManager(),
                'label' => $label,
                'required' => $required,
                'btn_edit'=>false,
                'sonata_help' => $sonata_help

        ]);
    }

    /**
     * @return BaseMediaAdmin
     */
    public function getMediaAdmin()
    {
        if (!$this->mediaAdmin) {
            $this->mediaAdmin = $this->container->get('sonata.media.admin.media');
        }

        return $this->mediaAdmin;
    }




}
