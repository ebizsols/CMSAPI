<?php
namespace App\Helper;
/**
 * This is helper is for Api response helper
 */


class ApiResponseHelper {

    const API_VERSION = "5.0V";

    const SUCCESS_CODE = "200";
    const SUCCESS_MSG = "Success";

    const ERR_GENERIC_CODE = -1;
    const ERR_GENERIC_MSG = 'Error!';
    const ERR_USER_REQUEST = 'User ID Required';
    const ERR_UNKNOWN = 'Some unknown error occur. Please try again.';

    const INVALID_LOGIN_CODE = '-100';
    const INVALID_LOGIN_MSG = 'Invalid Login';

    const TOO_MANY_LOGIN_CODE = '-101';
    const TOO_MANY_LOGIN_MSG = 'Too Many Login Attempts';

    const INVALID_TOKEN_CODE = '-401';
    const INVALID_TOKEN_MSG = 'Invalid bearer token';

    const USER_NOT_FOUND_CODE = '-404';
    const USER_NOT_FOUND_MSG = 'User not Found';

    const NOT_FOUND_CODE = '404';
    const ROLE_NOT_FOUND_MSG = 'Role not Found';
    const MENU_NOT_FOUND_MSG = 'Menu not Found';
    const ERR_DATA_NOT_FOUND = 'Data not Found';

    const REQUIRE_CODE = '402';
    const REQUIRE_ID_MSG = array('Id Require');
    const VALIDATION_MSG = 'Validation Error';

    const DELETE_CODE = '203';
    const DELETE_MSG = 'Deleted Successfully';



    const PASSWORD_RESET_MSG = 'Password reset successful';

    const EXPIRE_RESET_PASSWORD_CODE = '-300';

    const EXPIRE_RESET_PASSWORD_MSG = 'Reset password token expire';


    public static function withEveryResponse(){

        return [
            'version' => self::API_VERSION,
            'dateTime' => date("F j, Y, g:i a")
            ];

    }

    public static function responseArray($code = self::ERR_GENERIC_CODE, $message = self::ERR_GENERIC_MSG, $errors = false){

        return ['response'=>[
            'code' => $code,
            'message' => self::formatMessages($message),
            'errors'=> ($errors != false)? self::formatMessages($errors) : array(),
        ]];

    }

    public static function formatMessages($messages)
    {
        $newArray = array();

        if ($messages != null) {

            $messages = (is_string($messages))?array($messages) : $messages;

            foreach ($messages as $message) {

                if (is_array($message)) {

                    foreach ($message as $messageIn) {

                        $newArray[] = $messageIn;
                    }
                } else {

                    $newArray[] = $message;
                }
            }
        }
        return $newArray;
    }


}