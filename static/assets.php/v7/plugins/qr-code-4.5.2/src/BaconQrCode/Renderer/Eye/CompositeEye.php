<?php
declare(strict_types = 1);

namespace Endroid\QrCode\BaconQrCode\Renderer\Eye;

use Endroid\QrCode\BaconQrCode\Renderer\Path\Path;

/**
 * Combines the style of two different eyes.
 */
final class CompositeEye implements EyeInterface
{
    /**
     * @var EyeInterface
     */
    private $externalEye;

    /**
     * @var EyeInterface
     */
    private $internalEye;

    public function __construct(EyeInterface $externalEye, EyeInterface $internalEye)
    {
        $this->externalEye = $externalEye;
        $this->internalEye = $internalEye;
    }

    public function getExternalPath() : Path
    {
        return $this->externalEye->getExternalPath();
    }

    public function getInternalPath() : Path
    {
        return $this->internalEye->getInternalPath();
    }
}
