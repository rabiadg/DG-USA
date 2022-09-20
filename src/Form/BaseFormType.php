<?php

namespace App\Form;

use App\Entity\FrontUser;
use Psr\Container\ContainerInterface;
use Sonata\AdminBundle\Form\Type\ModelListType;
use Sonata\MediaBundle\Admin\BaseMediaAdmin;
use Sonata\MediaBundle\Model\MediaManagerInterface;
use Sonata\PageBundle\Model\SiteManagerInterface;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\UrlType;

class BaseFormType extends AbstractType
{
    protected $site = null;

    /**
     * @var SiteManagerInterface
     */
    protected $siteManager;
    protected $container;
    protected $manager;
    /**
     * @var MediaManagerInterface
     */
    protected $mediaManager;
    /**
     * @var BaseMediaAdmin
     */
    protected $mediaAdmin;


    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
        $this->manager = $this->container->get('doctrine')->getManager();
        $this->mediaManager = $this->container->get('sonata.media.manager.media');
    }

    protected function getMediaBuilder(FormBuilderInterface $builder, $name = 'mediaId', $label = 'form.label_media', $required = true, $sonata_help = null, $link_parameters = null)
    {
        // simulate an association ...
        $fieldDescription = $this->getMediaAdmin()->createFieldDescription('media', [
            'translation_domain' => 'SonataMediaBundle',
            'link_parameters' => $link_parameters
        ]);

        $fieldDescription->setAssociationAdmin($this->getMediaAdmin());
        $fieldDescription->setAdmin($this->getMediaAdmin());
        $fieldDescription->setOption('edit', 'list');
        /*$fieldDescription->setAssociationMapping([
            'fieldName' => 'media',
            'type' => ClassMetadataInfo::MANY_TO_ONE,
        ]);*/

        return $builder->create($name, ModelListType::class, [
            'sonata_field_description' => $fieldDescription,
            'class' => $this->getMediaAdmin()->getClass(),
            'model_manager' => $this->getMediaAdmin()->getModelManager(),
            'label' => $label,
            'btn_edit' => false,
            'required' => $required,
            'help' => $sonata_help,
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

    public function getSite()
    {

        if ($this->site == null) {
            $request = $this->container->get('request_stack')->getCurrentRequest();
            if ($this->siteManager) {
                $this->site = $this->siteManager->findOneBy(array('locale' => $request->getLocale()));
            } else {
                $DM = $this->getDoctrineManager();
                $this->site = $DM->getRepository('App\Application\Sonata\PageBundle\Entity\Site')
                    ->findOneBy(array('locale' => $request->getLocale()));
            }

        }

        return $this->site;


    }

    public function getDoctrineManager($manager = 'default')
    {
        return $this->getDoctrine()->getManager($manager);
    }

    public function getDoctrine()
    {
        return $this->container->get('doctrine');
    }

    public function getRequest()
    {
        return $this->container->get('request_stack')->getCurrentRequest();
    }

    public function getPage()
    {
        $request = $this->getRequest();
        $id = $request->attributes->get('id');
        $em = $this->getDoctrineManager();
        if ($request->attributes->get('_route') == 'admin_sonata_page_block_edit') {
            $block = $em->getRepository('App\Application\Sonata\PageBundle\Entity\Block')->find($id);
            $page = $block->getPage();
        } else {
            $page = $em->getRepository('App\Application\Sonata\PageBundle\Entity\Page')->find($id);
        }
        return $page;
    }

    public function getPageTemplate()
    {
        $page = $this->getPage();

        return $page->getTemplateCode();
    }

    public function getServicesPage()
    {
        $em = $this->getDoctrineManager();
        $page = $em->getRepository('App\Application\Sonata\PageBundle\Entity\Page')->findOneBy([
            'templateCode' => 'services',
            'parent' => $this->getPage()
        ]);
        return $page;
    }

    public function getBlockType()
    {
        $request = $this->getRequest();
        $type = $request->query->get('type');

        $em = $this->getDoctrineManager();
        if ($request->attributes->get('_route') == 'admin_sonata_page_block_edit') {
            $id = $request->attributes->get('id');
            $block = $em->getRepository('App\Application\Sonata\PageBundle\Entity\Block')->find($id);
            $type = $block->getType();
        }
        return $type;
    }
}
