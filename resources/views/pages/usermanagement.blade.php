@extends('layouts.index')

@section('content')

 


<div class="col-md-12" style= "margin-top: 30px">
<form method='post' action='/admin/save'>

     <!-- Message -->
     @if(Session::has('message'))
       <p >{{ Session::get('message') }}</p>
     @endif

 
     <!-- Add/List records -->
     <table border='1' style="background-color: rgb(192, 192, 192)">
       <tr>
         <th>Name</th>
         <th>Position</th>
         <th>Type</th>
         <th>ID Number</th>
         <th>Email</th>
         <th>Password</th>
         <th></th>
       </tr>
       <tr>
         {{ csrf_field() }}
       </tr>
       <!-- Add -->
       <tr>
         <td><input type='text' name='name'></td>
         <td><input type='text' name='position'></td>
         <td><input type='text' name='type'></td>
         <td><input type='text' name='username'></td>
         <td><input type='email' name='email'></td>
         <td><input type='password' name='password'></td>
         <td><input type='submit' name='submit' value='Add'></td>
       </tr>

       <!-- List -->
       @foreach($userData['data'] as $user)
       <tr>
         <td>{{ $user->name }}</td>
         <td>{{ $user->position }}</td>
         <td>{{ $user->type }}</td>
         <td>{{ $user->username }}</td>
         <td>{{ $user->email }}</td>
         <td>{{ $user->password }}</td>
         <td><a href='/admin/usermanagement{{ $user->id }}'>Update</a> <a href='/admin/deleteUser/{{ $user->id }}'>Delete</a></td>
       </tr>
       @endforeach
    </table>
  </form>

  <!-- Edit -->
  @if($userData['edit'])
  <form method='post' action='/admin/save'>
   <table>
     <tr>
       <td colspan='2'><h1>Edit record</h1></td>
     </tr>
     <tr>
       <td colspan="2">{{ csrf_field() }}</td>
     </tr>
     <tr>
       <td>Name</td>
       <td><input type='text' name='name' value='{{ $userData["editData"]->name }}'></td>
     </tr> 
     <tr>
       <td>Position</td>
       <td><input type='text' name='position' value='{{ $userData["editData"]->position }}' ></td>
     </tr>
     <tr>
       <td>Type</td>
       <td><input type='text' name='type' value='{{ $userData["editData"]->type }}' ></td>
     </tr>
     <tr>
       <td>Username</td>
       <td><input type='text' name='uname' readonly value='{{ $userData["editData"]->username }}' ></td>
     </tr>
     <tr>
       <td>Email</td>
       <td><input type='email' name='email' value='{{ $userData["editData"]->email }}' ></td>
     </tr>
     <tr>
       <td>Password</td>
       <td><input type='password' name='password' class="form-control @error('password') is-invalid @enderror" required autocomplete="new-password" value='{{ $userData["editData"]->password }}' ></td>
     </tr>
     <tr>
       <td>&nbsp;<input type='hidden' value='{{ $userData["edit"] }}' name='editid'></td>
       <td><input type='submit' name='submit' value='Submit'></td>
     </tr>
   </table>
  </form>
  @endif

</div>
             
    
@endsection