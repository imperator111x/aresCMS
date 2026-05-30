<?php

namespace App\Support;

/**
 * Discord CDN-Avatare (siehe https://discord.com/developers/docs/reference#image-formatting).
 */
class DiscordAvatar
{
    /**
     * Öffentliche Bild-URL für Profilbild-Download (Discord-CDN).
     */
    public static function cdnUrl(string $discordUserId, ?string $avatarHash): string
    {
        if ($avatarHash !== null && $avatarHash !== '') {
            $extension = str_starts_with($avatarHash, 'a_') ? 'gif' : 'png';

            return sprintf('https://cdn.discordapp.com/avatars/%s/%s.%s', $discordUserId, $avatarHash, $extension);
        }

        $id = (int) $discordUserId;
        $index = ($id >> 22) % 6;

        return sprintf('https://cdn.discordapp.com/embed/avatars/%d.png', $index);
    }
}
