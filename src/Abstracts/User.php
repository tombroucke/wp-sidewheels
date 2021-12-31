<?php
namespace Otomaties\Sidewheels\Abstracts;

abstract class User
{

    protected $user;

    public function __construct(\WP_User|int $user)
    {
        if (is_int($user)) {
            $this->user = get_user_by('ID', $user);
        } else {
            $this->user = $user;
        }
    }

    abstract public static function role() : string;

    public function getId() : int
    {
        return $this->user->ID;
    }

    public function firstName() : string
    {
        return $this->user->first_name;
    }

    public function lastName() : string
    {
        return $this->user->last_name;
    }

    public function name() : string
    {
        $firstName = $this->firstName();
        $lastName = $this->lastName();

        if ($firstName) {
            return "${firstName} ${lastName}";
        }

        return $this->user->display_name;
    }

    public function email() : string
    {
        return $this->user->user_email;
    }

    public function get(string $key, bool $single = true)
    {
        return get_user_meta($this->getId(), $key, $single);
    }

    public function set(string $key, $value, $prevValue = '')
    {
        return update_user_meta($this->getId(), $key, $value, $prevValue);
    }

    public function delete(string $key, $value = '')
    {
        return delete_user_meta($this->getId(), $key, $value);
    }

    public static function find() : array
    {
        $class = get_called_class();
        $args = [
        'role' => static::role(),
        ];
        return array_map(function ($user) use ($class) {
            return new $class($user);
        }, get_users($args));
    }

    public static function insert($args)
    {
        $class = get_called_class();
        $defaults = array(
        'user_login' => null,
        'user_pass' => null,
        'role' => static::role(),
        );

        $args = wp_parse_args($args, $defaults);
        $post_id = wp_insert_user($args);

        return new $class($post_id);
    }
}
