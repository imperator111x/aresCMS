<?php

namespace App\Support;

final class ReactionType
{
    public const LIKE = 'like';

    public const LOVE = 'love';

    public const FIRE = 'fire';

    public const WOW = 'wow';

    public const SAD = 'sad';

    /**
     * @return list<string>
     */
    public static function all(): array
    {
        return [
            self::LIKE,
            self::LOVE,
            self::FIRE,
            self::WOW,
            self::SAD,
        ];
    }

    /**
     * @return array<string, array{icon: string, label: string}>
     */
    public static function definitions(): array
    {
        return [
            self::LIKE => ['icon' => 'fa-thumbs-up', 'label' => 'Like'],
            self::LOVE => ['icon' => 'fa-heart', 'label' => 'Love'],
            self::FIRE => ['icon' => 'fa-fire', 'label' => 'Fire'],
            self::WOW => ['icon' => 'fa-face-surprise', 'label' => 'Wow'],
            self::SAD => ['icon' => 'fa-face-sad-tear', 'label' => 'Sad'],
        ];
    }

    public static function isValid(string $type): bool
    {
        return in_array($type, self::all(), true);
    }
}
