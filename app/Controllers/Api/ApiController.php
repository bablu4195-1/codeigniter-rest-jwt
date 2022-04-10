<?php

namespace App\Controllers\Api;

use CodeIgniter\RESTful\ResourceController;
use App\Models\UserModel;
use App\Models\BookModel;
use Exception;
use Firebase\JWT\JWT;
use Firebase\JWT\Key;

class ApiController extends ResourceController
{
    //post
    public function userRegister(){
       $rules = [
       "name" => "required",
       "email" =>"required|valid_email|is_unique[users.email]",
       "password "=>"required"
       ];
       if(!$this->validate($rules)){
        //error
         $response = [
            "status" => 500,
            "message" => $this->validator->getErrors(),
            "error" => true
         ];
       }
        else {
          $user_obj = new UserModel();
          $data = [
          "name" => $this->request->getVar("name"),    
          "email" => $this->request->getVar("email"),
          "password" => password_hash($this->request->getVar("password"), PASSWORD_DEFAULT)
         ];
         if($user_obj->insert($data)){
             //success
             $response = [
                "status" => 200,
                "message" => "User has been registered successfully",
                "error" => false,
                "data" => []
             ];

         } else {
             //failed to insert
             $response = [
                "status" => 500,
                "message" => $this->validator->getErrors(),
                "error" => true
             ];
         }
      }
      return $this->respond($response);
    }
    public function getKey()
    {
      return "ABCDEFGH";
    }
   //post
    public function userLogin(){
        $rules = [
           "email" => "required|valid_email",
            "password" => "required"
        ];
        if(!$this->validate($rules)){
            //error
            $response = [
                "status" => 500,
                "message" => $this->validator->getErrors(),
                "error" => true,
                "data" => []
            ];                         
        } else {
            $email = $this->request->getVar("email");
            $password = $this->request->getVar("password");

            $user_obj = new UserModel();
            $user_data = $user_obj -> where("email",$email)->first();
            if(!empty($user_data)){
             //user exists
            if(password_verify($password, $user_data["password"])){
            //password is correct
            $key = $this->getKey();
            $iat = time();
            $nbf = $iat;
            $exp = $iat + 300;
            $payload = [
                // "iss" => "The_claim",
                //  "aud" => "The_Aud",
                "iat" => $iat,
                "nbf" => $nbf,
                "exp" => $exp,
                "data" => $user_data
            ];
             $token = JWT::encode($payload, $key,"HS256");
             $response = [
                "status" => 200,
                "message" => "User has been logged in successfully",
                "error" => false,
                "data" => [
                    "token" => $token
                ]
             ];
            }else {
                //password is incorrect
                $response = [
                    "status" => 500,
                    "message" => "password is incorrect",
                    "error" => true,
                    "data" => []
                ];

            }
            }
            else {
                //user does not exist
                $response = [
                    "status" => 500,
                    "message" => $this->validator->getErrors()+"User does not exist",
                    "error" => true,
                    "data" => []
                ];
            }
        }
        return $this->respondCreated($response);
    }
    
    //get
    public function userProfile(){
     $key = $this->getKey();
     $auth = $this->request->getHeader("Authorization");
     $token = $auth->getValue();
     $decoded_data = JWT :: decode($token,new Key($key,"HS256"));
     $response = [
         "status" => 200,
         "message" => "User Profile has been fetched successfully",
         "error" => false,
         "data" => [
             'user' => $decoded_data,
             'id'=>$decoded_data->userdata->id
             ]
            ];
            
            return $this->respondCreated($response);
        }
        //post
        public function createBook(){
            //validation
            $rules = [
                "title" => "required",
                "price" => "required",
            ];
            if(!$this->validate($rules)){
                //error  
                $response = [
                    "status" => 500,
                    "message" => $this->validator->getErrors(),
                    "error" => true,
                    "data" => []
                ];
            } else {
                $key = $this->getKey();
                //no error
                $auth = $this->request->getHeader("Authorization");
                $token = $auth->getValue();
                $decoded_data = JWT :: decode($token,new Key($key,"HS256"));
                $user_id = $decoded_data->data->id;
                $book_obj = new BookModel();
                $data = [
                    "title" => $this->request->getVar("title"),
                    "price" => $this->request->getVar("price"),
                    "user_id" => $user_id
                ];
                if($book_obj->insert($data)){
                    //data has been saved
                    $response = [
                        "status" => 200,
                        "message" => "Book has been created successfully",
                        "error" => false,
                        "data" => []
                    ];
                } else {
                    //failed to save data
                    $response = [
                        "status" => 500,
                        "message" => "Failed to create book",
                        "error" => true,
                        "data" => []
                    ];
                }
            }
            return $this->respondCreated($response);
    }
    //get
    public function listBooks(){
        try{
            $key = $this->getKey();
            //no error
            $auth = $this->request->getHeader("Authorization");
            $token = $auth->getValue();
            $decoded_data = JWT :: decode($token,new Key($key,"HS256"));
            $user_id = $decoded_data->data->id;
            $book_obj = new bookmodel();
            $book_data = $book_obj->where("user_id",$user_id)->findAll();
            $response = [
              'status' => 200,
                'message' => 'Books has been fetched successfully',
                'error' => false,
                'data' => [
                    'books' => $book_data
                ]  
            ];
        } catch(exception $e){
            $response = [
                "status" => 500,
                "message" => $e->getMessage(),
                "error" => true,
                "data" => []
            ];
        }
       
        return $this->respondCreated($response);
    }
    //delete
    public function deleteBook($book_id){
        try{
            $key = $this->getKey();
            //no error
            $auth = $this->request->getHeader("Authorization");
            $token = $auth->getValue();
            $decoded_data = JWT :: decode($token,new Key($key,"HS256"));
            $user_id = $decoded_data->data->id;
            $book_obj = new bookmodel();
            $book_data = $book_obj->where([
                "user_id" => $user_id,
                "id" => $book_id,
            ])->first();
            if(!empty($book_data)){
            $book_obj->delete($book_id);
            //we have books
            $response = [
                "status" => 200,
                "message" => "Book has been deleted successfully",
                "error" => false,
                "data" => []
            ];
            } else {
            //we have no books
            $response = [
              'status' => 500,
                'message' => 'Books not existed',
                'error' => true,
                'data' => [
                    'books' => $book_data
                ]  
            ];
            }
        } catch(exception $e){
            $response = [
                "status" => 404,
                "message" => $e->getMessage(),
                "error" => true,
                "data" => []
            ];
        }
    }

}
