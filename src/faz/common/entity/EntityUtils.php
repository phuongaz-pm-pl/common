<?php

declare(strict_types=1);

namespace faz\common\entity;

use faz\common\utils\Image;
use Generator;
use matze\pathfinder\result\PathResult;
use matze\pathfinder\setting\Settings;
use matze\pathfinder\type\AsyncPathfinder;
use pocketmine\entity\Entity;
use pocketmine\entity\InvalidSkinException;
use pocketmine\entity\Location;
use pocketmine\entity\Skin;
use pocketmine\math\Vector3;
use pocketmine\world\Position;
use SOFe\AwaitGenerator\Await;

class EntityUtils {

    public static function parseSkin(string $texture, string $geometry, string $path, ?string $capData = "") : ?Skin {
        $texturePath = $path . $texture . ".png";
        $img = imagecreatefrompng($texturePath);
        $bytes = '';
        $imageSize = @getimagesize($texturePath);

        if ($imageSize === false || $imageSize[0] !== 64 || $imageSize[1] !== 64) {
            $skinImage = Image::dropSize($texturePath, 64, 64);
            imagepng($skinImage, $texturePath);
            return self::parseSkin($texture, $geometry, $path);
        }

        $l = $imageSize[1];

        for ($y = 0; $y < $l; $y++) {
            for ($x = 0; $x < 64; $x++) {
                $rgba = imagecolorat($img, $x, $y);
                $a = ((~($rgba >> 24)) << 1) & 0xff;
                $r = ($rgba >> 16) & 0xff;
                $g = ($rgba >> 8) & 0xff;
                $b = $rgba & 0xff;
                $bytes .= chr($r) . chr($g) . chr($b) . chr($a);
            }
        }

        imagedestroy($img);
        $geometryPath = $path . $geometry . ".json";

        try {
            return new Skin("Standard_CustomSlim", $bytes, $capData, "geometry." . $geometry , file_get_contents($geometryPath));
        } catch (InvalidSkinException) {
            return null;
        }
    }

    public function findRandomPosition(Location $location, int $range, int $maxStep = 10, int $currentStep = 0) : ?Position{
        $x = $location->x + mt_rand(-$range, $range);
        $z = $location->z + mt_rand(-$range, $range);
        $world = $location->getWorld();
        $highestBlock = $world->getHighestBlockAt($x, $z);

        if($highestBlock - $location->y > 10) {
            if($currentStep >= $maxStep) {
                return null;
            }
            return $this->findRandomPosition($location, $range, $maxStep, $currentStep + 1);
        }

        return new Position($x, $highestBlock + 1, $z, $world);
    }

    public function canMoveTo(Entity $entity, Position $position) : Generator {
        $settings = Settings::get()->setJumpHeight((int)$entity->getJumpVelocity());
        $pathFinder = new AsyncPathfinder($settings, $entity->getWorld());
        return Await::promise(function($resolve) use ($pathFinder, $entity, $position) {
            $pathFinder->findPath($entity->getPosition()->asVector3(), $position->asVector3(), function(?PathResult $result) use ($resolve) {
                return $result instanceof PathResult ? $resolve(true) : $resolve(false);
            });
        });
    }
}