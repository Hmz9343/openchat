<?php
/**
 * Search Class Doc Comment
 *
 * PHP version 5
 *
 * @category PHP
 * @package  OpenChat
 * @author   Ankit Jain <ankitjain28may77@gmail.com>
 * @license  The MIT License (MIT)
 * @link     https://github.com/ankitjain28may/openchat
 */
namespace ChatApp;

require_once dirname(__DIR__).'/vendor/autoload.php';
use ChatApp\Time;
use mysqli;
use Dotenv\Dotenv;
$dotenv = new Dotenv(dirname(__DIR__));
$dotenv->load();

/**
 * Search for the user
 *
 * @category PHP
 * @package  OpenChat
 * @author   Ankit Jain <ankitjain28may77@gmail.com>
 * @license  The MIT License (MIT)
 * @link     https://github.com/ankitjain28may/openchat
 */
class Search
{
    /*
    |--------------------------------------------------------------------------
    | Search Class
    |--------------------------------------------------------------------------
    |
    | Search for the user.
    |
    */

    protected $connect;
    protected $array;
    protected $obTime;

    /**
     * Create a new class instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->connect = new mysqli(
            getenv('DB_HOST'),
            getenv('DB_USER'),
            getenv('DB_PASSWORD'),
            getenv('DB_NAME')
        );
        $this->obTime = new Time();
        $this->array = array();
    }

    /**
     * Fetch Search Item from the DB
     *
     * @param object $suggestion To store user id and suggestion value
     *
     * @return string
     */
    public function searchItem($suggestion)
    {
        $userId = $suggestion->userId;
        $suggestion = trim($suggestion->value);
        $flag = 0;
        if (!empty($userId) && !empty($suggestion)) {
            $query = "SELECT * FROM login where login_id != '$userId' and
                        name like '$suggestion%' ORDER BY name DESC";
            if ($result = $this->connect->query($query)) {
                if ($result->num_rows > 0) {
                    while ($row = $result->fetch_assoc()) {
                        $check_id = $row["login_id"];
                        $query = "SELECT * from total_message where (
                            user1 = '$check_id' and user2 = '$userId'
                            ) or (user2 = '$check_id' and user1 = '$userId')";

                        if ($result1 = $this->connect->query($query)) {
                            if ($result1->num_rows > 0) {
                                $fetch = $result1->fetch_assoc();
                                $fetch['time'] = $this->obTime->timeConversion(
                                    $fetch['time']
                                );

                                $this->array = array_merge(
                                    $this->array,
                                    [[
                                    'time' => $fetch['time'],
                                    'username' => $row['username'],
                                    'name' => $row['name'],
                                    'login_status' => $row['login_status']
                                    ]]
                                );
                                $flag = 1;
                            }
                        }
                    }
                }
            }
            if ($flag != 0) {
                $this->array = array_merge([], ["Search" => $this->array]);
                return json_encode($this->array);
            }
            return json_encode(["Search" => "Not Found"]);
        }
        return json_encode(["Search" => "Not Found"]);
    }
}
