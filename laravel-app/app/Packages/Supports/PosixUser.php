<?php
/**
 * User: paul
 * Date: 2025/3/6
 * Time: 16:38
 */

namespace App\Packages\Supports;

use Illuminate\Support\Arr;

/**
 * Class PosixUser
 *
 * @package  App\Packages\Supports
 * @Author   : paul
 * @DateTime : 2025/3/6 16:48
 */
class PosixUser
{
    /**
     * @var
     * @Author   : paul
     * @DateTime : 2025/3/6 16:48
     */
    public static $current_user_id;

    /**
     * @var
     * @Author   : paul
     * @DateTime : 2025/3/6 16:48
     */
    public static $current_user_name;

    /**
     * @return int
     * @Author   : paul
     * @DateTime : 2025/3/6 16:48
     */
    public static function getCurrentUserId(): int
    {
        if (is_null(self::$current_user_id) == false) {
            return self::$current_user_id;
        }
        self::$current_user_id = posix_getuid();
        return self::$current_user_id;
    }

    /**
     * @return array|false
     * @Author   : paul
     * @DateTime : 2025/3/6 16:48
     */
    public static function getCurrentUserName()
    {
        if (is_null(self::$current_user_name) == false) {
            return self::$current_user_name;
        }
        $info = posix_getpwuid(self::getCurrentUserId());
        if (is_array($info)) {
            self::$current_user_name = Arr::get($info, 'name');
        }
        return self::$current_user_name;
    }

    /**
     * @return bool
     * @Author   : paul
     * @DateTime : 2025/3/6 16:48
     */
    public static function isRoot(): bool
    {
        return (self::getCurrentUserId() === 0);
    }
}
