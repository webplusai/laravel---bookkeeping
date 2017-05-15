@extends('layouts.app')

@section('content')
<div class="container">
    <div class="row">
        <div class="col-md-8 col-md-offset-2">
            <div class="panel panel-default">
                <div class="panel-heading">Dashboard</div>

                <div class="panel-body">
                    You are logged in!
                </div>
            </div>
        </div>
    </div>
</div>
<a href="saudisms/whm/api/export_to_pdf?content=<h1>Hi</h1>"> Generate PDF </a>
<form id="printContent" class="" action="saudisms/whm/api/export_to_pdf" method="GET">
                        <input type="text" name="content" value="<h1> Hi </h1>">
                        <button type="submit"> Generate PDF </button>
                      </form>
@endsection
