<?php

namespace App\Http\Controllers;

use Session;
use Illuminate\Http\Request;
use App\UserManagement;
use Illuminate\Support\Facades\Hash;

class UserManagementController extends Controller
{
    public function index($id=0){
 
        // Fetch all records
        $userData['data'] = UserManagement::getuserData();
     
        $userData['edit'] = $id;
    
        // Fetch edit record
        if($id>0){
          $userData['editData'] = UserManagement::getuserData($id);
        }
    
        // Pass to view
        return view('pages.usermanagement')->with("userData",$userData);
      }
    
      public function save(Request $request){
     
        if ($request->input('submit') != null ){
    
          // Update record
          if($request->input('editid') !=null ){
            $name = $request->input('name');
            $email = $request->input('email');
            $editid = $request->input('editid');
    
            if($name !='' && $email != ''){
               $data = array('name'=>$name,"email"=>$email);
     
               // Update
               UserManagement::updateData($editid, $data);
    
               Session::flash('message','Update successfully.');
     
            }
     
          }else{ // Insert record
             $name = $request->input('name');
             $position = $request->input('position');
             $username = $request->input('username');
             $type = $request->input('type');
             $email = $request->input('email');
             $password1 = $request->input('password');
             $password = Hash::make($password1);
    
             if($name !='' && $username !='' && $email != ''){
                $data = array('name'=>$name, 'position'=>$position, "username"=>$username,"email"=>$email, "password"=>$password, "type"=>$type);
     
                // Insert
                $value = UserManagement::insertData($data);
                if($value){
                  Session::flash('message','Insert successfully.');
                }else{
                  Session::flash('message','Username already exists.');
                }
     
             }
          }
     
        }
        return redirect()->action('UserManagementController@index',['id'=>0]);
      }
    
      public function deleteUser($id=0){
    
        if($id != 0){
          // Delete
          UserManagement::deleteData($id);
    
          Session::flash('message','Delete successfully.');
          
        }
        return redirect()->action('UserManagementController@index',['id'=>0]);
      }
}
