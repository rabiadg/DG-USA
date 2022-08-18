<?php
# src/Application/Sonata/MediaBundle/Provider/FileProvider.php
namespace App\Application\Sonata\MediaBundle\Provider;

use Sonata\Form\Validator\ErrorElement;
use Sonata\MediaBundle\Provider\FileProvider as BaseFileProvider;
use Gaufrette\Filesystem;
use Sonata\AdminBundle\Form\FormMapper;
use Sonata\MediaBundle\Provider\Metadata;
use Sonata\MediaBundle\CDN\CDNInterface;
use Sonata\MediaBundle\Extra\ApiMediaFile;
use Sonata\MediaBundle\Generator\GeneratorInterface;
use Sonata\MediaBundle\Metadata\MetadataBuilderInterface;
use Sonata\MediaBundle\Model\MediaInterface;
use Sonata\MediaBundle\Thumbnail\ThumbnailInterface;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\FormBuilder;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpFoundation\File\File;
use Symfony\Component\HttpFoundation\File\MimeType\ExtensionGuesser;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\StreamedResponse;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\Constraints\NotNull;
use Symfony\Component\Validator\Constraints as Assert;


class FileProvider extends BaseFileProvider
{
protected array $allowedExtensions;

protected array $allowedMimeTypes;

protected ?MetadataBuilderInterface $metadata = null;

    /**
     * @param string $name
     * @param Filesystem $filesystem
     * @param CDNInterface $cdn
     * @param GeneratorInterface $pathGenerator
     * @param ThumbnailInterface $thumbnail
     * @param array $allowedExtensions
     * @param array $allowedMimeTypes
     * @param MetadataBuilderInterface $metadata
     */
    public function __construct($name, Filesystem $filesystem, CDNInterface $cdn, GeneratorInterface $pathGenerator, ThumbnailInterface $thumbnail, array $allowedExtensions = array(), array $allowedMimeTypes = array(), MetadataBuilderInterface $metadata = null)
    {
        parent::__construct($name, $filesystem, $cdn, $pathGenerator, $thumbnail);

        $this->allowedExtensions = $allowedExtensions;
        $this->allowedMimeTypes = $allowedMimeTypes;
        $this->metadata = $metadata;
    }

    /**
     * {@inheritdoc}
     */
    public function prePersist(MediaInterface $media): void
    {
        $media->setCreatedAt(new \Datetime());
        $media->setUpdatedAt(new \Datetime());
    }

    /**
     * {@inheritdoc}
     */
    public function buildCreateForm(FormMapper $formMapper): void
    {
        $formMapper->add('binaryContent', FileType::class, array(
            'label' => '(Max Size: 100MB), Allowed file types (doc/pdf/xls/docx)',
            'constraints' => array(
                new NotBlank(),
                new NotNull(),
            ),
        ));
        //parent::buildCreateForm($formMapper);
    }

    /**
     * {@inheritdoc}
     */
    public function buildEditForm(FormMapper $formMapper): void
    {
        $formMapper->add('name');
        //$formMapper->add('enabled', null, array('required' => false));
        //$formMapper->add('authorName');
        //$formMapper->add('cdnIsFlushable');
        //$formMapper->add('description');
        //$formMapper->add('copyright');
        //$formMapper->add('binaryContent', FileType::class, array('required' => false));

        $formMapper->add('binaryContent', FileType::class, array('required' => false,
            'label' => '(Max Size: 100MB), Allowed file types (doc/pdf/xls/docx)',
            'constraints' => array(
                new Assert\File(array(
                    'maxSize' => '102400k',
                ))
            )
        ));
        //parent::buildEditForm($formMapper);
    }

    public function validate(ErrorElement $errorElement, MediaInterface $media): void
    {
        $binaryContent= $media->getBinaryContent();
        if ($binaryContent->getPath() == "") {
            $errorElement
                ->with('binaryContent')
                ->addViolation('Invalid File ')
                ->end();
        } elseif ($media->getId() == null) {
            $errorElement
                ->with('binaryContent')
                ->addConstraint(new Assert\NotBlank())
                ->end();
        } elseif ($media->getBinaryContent() != null && $media->getBinaryContent()->getSize() > 102400000) {
            $errorElement
                ->with('binaryContent')
                ->addViolation('Maximum file size is 100MB please upload image less than or equal to 100MB')
                ->end();
        } elseif ($media->getBinaryContent() == null && $media->getId() == null) {
            $errorElement
                ->with('binaryContent')
                ->addViolation('Please select valid image file')
                ->end();
        }

        parent::validate($errorElement, $media);
    }

    public function updateMetadata(MediaInterface $media, bool $force = true): void
    {
        if (!$media->getBinaryContent() instanceof \SplFileInfo) {
            // this is now optimized at all!!!
            $path = tempnam(sys_get_temp_dir(), 'sonata_update_metadata_');
            if (false === $path) {
                throw new \InvalidArgumentException(sprintf('Unable to generate temporary file name for media %s.', $media->getId() ?? ''));
            }

            $fileObject = new \SplFileObject($path, 'w');
            $fileObject->fwrite($this->getReferenceFile($media)->getContent());
        } else {
            $fileObject = $media->getBinaryContent();
        }

        $media->setSize($fileObject->getSize());
    }

    /**
     * Set the file contents for an image.
     */
    protected function setFileContents(MediaInterface $media, ?string $contents = null): void
    {
        $providerReference = $media->getProviderReference();

        if (null === $providerReference) {
            throw new \RuntimeException(sprintf(
                'Unable to generate path to file without provider reference for media "%s".',
                (string)$media
            ));
        }

        $file = $this->getFilesystem()->get(
            sprintf('%s/%s', $this->generatePath($media), $providerReference),
            true
        );

        $metadata = null !== $this->metadata ? $this->metadata->get($media, $file->getName()) : [];

        if (null !== $contents) {
            $file->setContent($contents, $metadata);

            return;
        }

        $binaryContent = $media->getBinaryContent();
        if ($binaryContent instanceof File) {
            $path = false !== $binaryContent->getRealPath() ? $binaryContent->getRealPath() : $binaryContent->getPathname();
            $fileContents = file_get_contents($path);

            if (false === $fileContents) {
                throw new \RuntimeException(sprintf('Unable to get file contents for media %s', $media->getId() ?? ''));
            }

            $file->setContent($fileContents, $metadata);

            return;
        }
    }
}