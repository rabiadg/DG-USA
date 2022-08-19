<?php


namespace App\Application\Sonata\PageBundle\Block;
use Doctrine\ORM\Mapping\ClassMetadataInfo;
use Sonata\AdminBundle\Form\Type\ModelListType;
use Sonata\BlockBundle\Block\Service\AbstractBlockService;
use Sonata\BlockBundle\Block\Service\EditableBlockService;
use Sonata\BlockBundle\Meta\Metadata;
use Sonata\BlockBundle\Meta\MetadataInterface;
use Sonata\BlockBundle\Model\BlockInterface;
use Sonata\CoreBundle\Model\ManagerInterface;
use Sonata\Form\Validator\ErrorElement;
use Sonata\MediaBundle\Admin\BaseMediaAdmin;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\Templating\EngineInterface;
use Twig\Environment;
use Sonata\BlockBundle\Form\Mapper\FormMapper;

class BaseBlockService extends AbstractBlockService implements EditableBlockService
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

    /**
     * @var Environment
     */
    private $twig;

    public function __construct(Environment $twig, ContainerInterface $container)
    {
        $this->container = $container;
        $this->twig = $twig;
        $this->manager = $this->container->get('doctrine')->getManager();
        //$this->mediaManager = $this->container->get('sonata.media.manager.media');
        parent::__construct($twig, $container);
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

    public function configureEditForm(FormMapper $form, BlockInterface $block): void{

    }

    public function configureCreateForm(FormMapper $form, BlockInterface $block): void {

    }

    public function validate(ErrorElement $errorElement, BlockInterface $block): void{

    }

    public function getMetadata(): MetadataInterface{
        return new Metadata($this->getName(), ($this->getName()), false, 'SonataBlockBundle', [
                    'class' => 'fa fa-file-text-o',
                ]);
    }

}
