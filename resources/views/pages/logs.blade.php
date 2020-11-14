@extends('layouts.index')

@section('content')
<div id="searchcontainer" class="container" style="text-align: right">
    <input type="text" id="search" placeholder="Search Disconnected Accounts" onkeyup="searchFun()" style="margin-top:20px">
</div>

<div class="container" style="margin-top: 30px">
        <div class="row">
            <div class="col-md-12"  style="text-align:center">
                <table id= "disaccountstable" class="table table-striped" style="background-color: rgb(192, 192, 192)">
                    <thead>
                        <tr>
                          <th>Account Number</th>
                          <th>Account Name</th>
                          <th>Disconnection Date-Time</th>
                          <th>Reconnection Date-Time</th>
                        </tr>
                    </thead>
                    <tbody>
                    @foreach($logs as $log)
                        <tr>
                            <td>{{$log->account_number}}</td>
                            <td>{{$log->account_name}}</td>
                            <td>{{$log->disconnection_date}}</td>
                            <td>{{$log->reconnection_date}}</td>
                        </tr>
                    @endforeach
                        
                    </tbody>
                </table>
            </div>
        </div>
</div>

<script>
    const searchFun = () =>{
        let filter = document.getElementById('search').value.toUpperCase();
        let disaccountstable = document.getElementById('disaccountstable');
        let tr= disaccountstable.getElementsByTagName('tr');

        
        for (var i=0; i<tr.length; i++){
            let td =tr[i];

            if (td){
                    for (k=0; k<6; k++){
                        let tx=td.getElementsByTagName('td')[k];

                        if (tx){
                            let textvalue = td.textContent || td.innerHTML;

                            if (textvalue.toUpperCase().indexOf(filter) > -1){
                                tr[i].style.display = "";
                            }else{
                                tr[i].style.display = "none";
                            }
                        }
                    }
                }
            }
        }
</script>
@endsection 