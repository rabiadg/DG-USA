<?php

namespace App\Application\Sonata\MediaBundle\Provider;

use Sonata\Form\Validator\ErrorElement;
use Sonata\MediaBundle\Provider\FileProvider as BaseFileProvider;
use Gaufrette\Filesystem;
use Sonata\AdminBundle\Form\FormMapper;
use Sonata\MediaBundle\CDN\CDNInterface;
use Sonata\MediaBundle\Generator\GeneratorInterface;
use Sonata\MediaBundle\Metadata\MetadataBuilderInterface;
use Sonata\MediaBundle\Model\MediaInterface;
use Sonata\MediaBundle\Thumbnail\ThumbnailInterface;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\HttpFoundation\File\File;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\Constraints\NotNull;

class SVGProvider extends BaseFileProvider
{
protected array $allowedExtensions;

    protected array $allowedMimeTypes;

    protected ?MetadataBuilderInterface $metadata = null;

    public function __construct($name, Filesystem $filesystem, CDNInterface $cdn, GeneratorInterface $pathGenerator, ThumbnailInterface $thumbnail, array $allowedExtensions = array(), array $allowedMimeTypes = array(), MetadataBuilderInterface $metadata = null)
    {
        parent::__construct($name, $filesystem, $cdn, $pathGenerator, $thumbnail);

        $this->allowedExtensions = $allowedExtensions;
        $this->allowedMimeTypes = $allowedMimeTypes;
        $this->metadata = $metadata;
    }

    public function buildCreateForm(FormMapper $formMapper): void
    {
        $formMapper->add('binaryContent', FileType::class, array(
            'label' => 'Upload SVG file only',
            'constraints' => array(
                new NotBlank(),
                new NotNull(),
            ),
        ));
    }

    public function buildEditForm(FormMapper $formMapper): void
    {
        $formMapper->add('name');
        //$formMapper->add('enabled', null, array('required' => false));
        $formMapper->add('binaryContent', FileType::class, array('required' => false,
            'label' => 'Image Max size(100 MB) Allowed image types(svg)',
        ));
    }

    /**
     * {@inheritdoc}
     */
    public function validate(ErrorElement $errorElement, MediaInterface $media): void
    {

        if (!$media->getBinaryContent() instanceof \SplFileInfo) {
            return;
        }

        if ($media->getBinaryContent() instanceof UploadedFile) {
            $fileName = $media->getBinaryContent()->getClientOriginalName();
        } elseif ($media->getBinaryContent() instanceof File) {
            $fileName = $media->getBinaryContent()->getFilename();
        } else {
            throw new \RuntimeException(sprintf('Invalid binary content type: %s', get_class($media->getBinaryContent())));
        }

        if (!in_array(strtolower(pathinfo($fileName, PATHINFO_EXTENSION)), $this->allowedExtensions['allowed_extensions'])) {
            $errorElement
                ->with('binaryContent')
                ->addViolation('Invalid extensions')
                ->end();
        } elseif (!in_array($media->getBinaryContent()->getMimeType(), $this->allowedMimeTypes['allowed_mime_types'])) {
            $errorElement
                ->with('binaryContent')
                ->addViolation('Invalid mime type : ' . $media->getBinaryContent()->getMimeType())
                ->end();
        } elseif ($media->getBinaryContent() != null && $media->getBinaryContent()->getSize() > 102400000) {
            $errorElement
                ->with('binaryContent')
                ->addViolation('Maximum file size is 100MB please upload image less than or equal to 100MB')
                ->end();
        }
    }

}
