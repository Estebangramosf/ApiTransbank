<!DOCTYPE html>
<html lang="en">

<head>

  <meta charset="utf-8">
  <meta http-equiv="X-UA-Compatible" content="IE=edge">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <meta name="description" content="">
  <meta name="author" content="">
  {!!Html::favicon('favicon.png')!!}
  <title>@yield('title') | ApiTransbank</title>

  <!-- Bootstrap Core CSS -->
  {!!Html::style('css/style.css')!!}
  {!!Html::style('css/bootstrap.min.css')!!}


    <!-- Custom CSS -->
  {!!Html::style('css/sb-admin.css')!!}

    <!-- Morris Charts CSS -->
  {!!Html::style('css/plugins/morris.css')!!}

    <!-- Custom Fonts -->
  {!!Html::style('font-awesome/css/font-awesome.min.css')!!}

    <!-- HTML5 Shim and Respond.js IE8 support of HTML5 elements and media queries -->
  <!-- WARNING: Respond.js doesn't work if you view the page via file:// -->
  <!--[if lt IE 9]>
  {!!Html::script('js/html5shiv.js')!!}
  {!!Html::script('js/respond.min.js')!!}
  <![endif]-->
</head>

<body>

<div id="wrapper">

  <!-- Navigation -->
  <nav class="navbar navbar-inverse navbar-fixed-top" role="navigation">
    <!-- Brand and toggle get grouped for better mobile display -->
    <div class="navbar-header">

      <button type="button" class="navbar-toggle" style="border-color: #7D7C7C;color:#7D7C7C;" data-toggle="collapse" data-target=".navbar-ex1-collapse">
        Menu <i class="sr-only"></i>
      </button>
      {{--
      <a class="navbar-brand page-scroll" href="#page-top">
        <i class="fa fa-play-circle"></i> <span class="light" style="font-family: Coolvetica;">SkilledStudio</span>
      </a>
      --}}

      {{--
      <button type="button" class="navbar-toggle" data-toggle="collapse" data-target=".navbar-ex1-collapse">
        <span class="sr-only">Toggle navigation</span>
        <span class="icon-bar"></span>
        <span class="icon-bar"></span>
        <span class="icon-bar"></span>
      </button>
      --}}
        <!-- Branding Image -->
      <a class="navbar-brand" href="{{ url('/') }}">
        ApiTransbank
      </a>
    </div>
    <!-- Top Menu Items -->
    <ul class="{{--nav navbar-nav navbar-right--}}nav navbar-right top-nav">
      <!-- Authentication Links -->
      {{--
        <li><a href="{{ url('/login') }}">Login</a></li>
        <li><a href="{{ url('/register') }}">Register</a></li>
      --}}

    </ul>
    {{--
<ul class="nav navbar-right top-nav">
      <li class="dropdown">
        <a href="#" class="dropdown-toggle" data-toggle="dropdown"><i class="fa fa-envelope"></i> <b class="caret"></b></a>



        <ul class="dropdown-menu message-dropdown">
          <li class="message-preview">
            <a href="#">
              <div class="media">
                <span class="pull-left">
                    <img class="media-object" src="http://placehold.it/50x50" alt="">
                </span>
                <div class="media-body">
                  <h5 class="media-heading"><strong>John Smith</strong>
                  </h5>
                  <p class="small text-muted"><i class="fa fa-clock-o"></i> Yesterday at 4:32 PM</p>
                  <p>Lorem ipsum dolor sit amet, consectetur...</p>
                </div>
              </div>
            </a>
          </li>
          <li class="message-preview">
            <a href="#">
              <div class="media">
                <span class="pull-left">
                    <img class="media-object" src="http://placehold.it/50x50" alt="">
                </span>
                <div class="media-body">
                  <h5 class="media-heading"><strong>John Smith</strong>
                  </h5>
                  <p class="small text-muted"><i class="fa fa-clock-o"></i> Yesterday at 4:32 PM</p>
                  <p>Lorem ipsum dolor sit amet, consectetur...</p>
                </div>
              </div>
            </a>
          </li>
          <li class="message-preview">
            <a href="#">
              <div class="media">
                <span class="pull-left">
                    <img class="media-object" src="http://placehold.it/50x50" alt="">
                </span>
                <div class="media-body">
                  <h5 class="media-heading"><strong>John Smith</strong>
                  </h5>
                  <p class="small text-muted"><i class="fa fa-clock-o"></i> Yesterday at 4:32 PM</p>
                  <p>Lorem ipsum dolor sit amet, consectetur...</p>
                </div>
              </div>
            </a>
          </li>
          <li class="message-footer">
            <a href="#">Read All New Messages</a>
          </li>
        </ul>
      </li>
      {{--
      <li class="dropdown">
        <a href="#" class="dropdown-toggle" data-toggle="dropdown"><i class="fa fa-bell"></i> <b class="caret"></b></a>
        <ul class="dropdown-menu alert-dropdown">
          <li>
            <a href="#">Alert Name <span class="label label-default">Alert Badge</span></a>
          </li>
          <li>
            <a href="#">Alert Name <span class="label label-primary">Alert Badge</span></a>
          </li>
          <li>
            <a href="#">Alert Name <span class="label label-success">Alert Badge</span></a>
          </li>
          <li>
            <a href="#">Alert Name <span class="label label-info">Alert Badge</span></a>
          </li>
          <li>
            <a href="#">Alert Name <span class="label label-warning">Alert Badge</span></a>
          </li>
          <li>
            <a href="#">Alert Name <span class="label label-danger">Alert Badge</span></a>
          </li>
          <li class="divider"></li>
          <li>
            <a href="#">View All</a>
          </li>
        </ul>
      </li>
      <li class="dropdown">
        <a href="#" class="dropdown-toggle" data-toggle="dropdown"><i class="fa fa-user"></i> John Smith <b class="caret"></b></a>
        <ul class="dropdown-menu">
          <li>
            <a href="#"><i class="fa fa-fw fa-user"></i> Profile</a>
          </li>
          <li>
            <a href="#"><i class="fa fa-fw fa-envelope"></i> Inbox</a>
          </li>
          <li>
            <a href="#"><i class="fa fa-fw fa-gear"></i> Settings</a>
          </li>
          <li class="divider"></li>
          <li>
            <a href="#"><i class="fa fa-fw fa-power-off"></i> Log Out</a>
          </li>
        </ul>
      </li>
    </ul>
    --}}


      <!-- Sidebar Menu Items - These collapse to the responsive navigation menu on small screens -->

      {!!Html::style('css/style.css')!!}
      <div class="collapse navbar-collapse navbar-ex1-collapse">
        <ul class="nav navbar-nav side-nav">

          <li class="{!! Request::path()=="users"?'active':'' !!}">
            <a href="{!! url('/users') !!}"><i class="fa fa-fw fa-dashboard"></i>
              <div align="center">
                <img class="out-dashboard-item" style="float:left;"
                     src="{!! asset('img/glyphicons/glyphicons/png/glyphicons-4-user.png') !!}" alt="">
                Usuarios
              </div>
            </a>
          </li>
          {{--
                    <li class="{!! Request::path()=="item1"?'active':'' !!}">
            <a href="{!! url('/#!') !!}"><i class="fa fa-fw fa-dashboard"></i>
              <div align="center">
                <img class="out-dashboard-item" style="float:left;"
                     src="{!! asset('img/glyphicons/glyphicons/png/glyphicons-43-pie-chart.png') !!}" alt="">
                Item 2
              </div>
            </a>
          </li>

          <li class="{!! Request::path()=="item2"?'active':'' !!}">
            <a href="{!! url('/#!') !!}"><i class="fa fa-fw fa-dashboard"></i>
              <div align="center">
                <img class="out-dashboard-item" style="float:left;"
                     src="{!! asset('img/glyphicons/glyphicons/png/glyphicons-137-cogwheel.png') !!}" alt="">
                Item 3
              </div>
            </a>
          </li>

          <li class="{!! Request::path()=="item3"?'active':'' !!}">
            <a href="{!! url('/#!') !!}"><i class="fa fa-fw fa-dashboard"></i>
              <div align="center">
                <img class="out-dashboard-item" style="float:left;"
                     src="{!! asset('img/glyphicons/glyphicons/png/glyphicons-40-notes.png') !!}" alt="">
                Item 4
              </div>
            </a>
          </li>

          <li class="{!! Request::path()=="item4"?'active':'' !!}">
            <a href="{!! url('/#!') !!}"><i class="fa fa-fw fa-dashboard"></i>
              <div align="center">
                <img class="out-dashboard-item" style="float:left;"
                     src="{!! asset('img/glyphicons/glyphicons/png/glyphicons-9-film.png') !!}" alt="">
                Item 5
              </div>
            </a>
          </li>

          <li class="{!! Request::path()=="item5"?'active':'' !!}">
            <a href="{!! url('/#!') !!}"><i class="fa fa-fw fa-dashboard"></i>
              <div align="center">
                <img class="out-dashboard-item" style="float:left;"
                     src="{!! asset('img/glyphicons/glyphicons/png/glyphicons-12-camera.png') !!}" alt="">
                Item 6
              </div>
            </a>
          </li>
          --}}


          {{--
          <li>
            <a href="charts.html"><i class="fa fa-fw fa-bar-chart-o"></i> Charts</a>
          </li>
          <li>
            <a href="tables.html"><i class="fa fa-fw fa-table"></i> Tables</a>
          </li>
          <li>
            <a href="forms.html"><i class="fa fa-fw fa-edit"></i> Forms</a>
          </li>
          <li>
            <a href="bootstrap-elements.html"><i class="fa fa-fw fa-desktop"></i> Bootstrap Elements</a>
          </li>
          <li>
            <a href="bootstrap-grid.html"><i class="fa fa-fw fa-wrench"></i> Bootstrap Grid</a>
          </li>
          <li>
            <a href="javascript:;" data-toggle="collapse" data-target="#demo"><i class="fa fa-fw fa-arrows-v"></i> Dropdown <i class="fa fa-fw fa-caret-down"></i></a>
            <ul id="demo" class="collapse">
              <li>
                <a href="#">Dropdown Item</a>
              </li>
              <li>
                <a href="#">Dropdown Item</a>
              </li>
            </ul>
          </li>
          <li>
            <a href="blank-page.html"><i class="fa fa-fw fa-file"></i> Blank Page</a>
          </li>
          <li>
            <a href="index-rtl.html"><i class="fa fa-fw fa-dashboard"></i> RTL Dashboard</a>
          </li>
          --}}
        </ul>
      </div>



        <!-- /.navbar-collapse -->
  </nav>

  <div id="page-wrapper">
    @yield('content')
  </div>

  <!-- /#page-wrapper -->


  <div class="container-fluid" style="background-color: #fff;padding: 280px;padding-bottom: 20px;">
    <footer class="site-footer">
      <div class="row">
        <div class="col-xs-12 col-sm-12 col-md-12 col-lg-12">
          {{--
           <img style="padding: 0;" width="240" alt="Imagen corfo" src= "{!!URL::to('img/footer/corfo.png')!!}" class="img-responsive-centered"/></a>
          --}}
        </div>
        <div class="col-xs-12 col-sm-12 col-md-12 col-lg-12" style="text-align: center;border-top:1px solid #f5f5f5;">
          {{--
          <span><a href="#!"></a></span>
          <span><a href="#!"><img  src= "{!!URL::to('img/footer/icono_twitter.png')!!}" class=""/></a></span>
          <span><a href="#!"><img  src= "{!!URL::to('img/footer/ico_instagram.png')!!}" class=""/></a></span>
          <span><a href="#!"><img  src= "{!!URL::to('img/footer/icono_youtube.png')!!}" class=""/></a></span>
          --}}
          <br>
          <br>
          <span><a class="btn-link" href="{!!URL::to('/#!/')!!}">Item footer 1</a></span>
          <span class="required">\</span>
          <span><a class="btn-link" href="{!!URL::to('/#!/')!!}">Item footer 2</a></span>
          <span class="required">\</span>
          <span><a class="btn-link" href="{!!URL::to('/#!/')!!}">Item footer 3</a></span>
          <span class="required">\</span>
          <span><a class="btn-link" href="{!!URL::to('/#!/')!!}">Item footer 4</a></span>
          <span class="required">\</span>
          <span><a class="btn-link" href="{!!URL::to('/#!/')!!}">Item footer 5</a></span>
          <span class="required">\</span>
          <span><a class="btn-link" href="{!!URL::to('/#!/')!!}">Item footer 6</a></span>
          <br><span> - Copyright© ~ dev.apitransbank.com - {!! date('Y') !!} </span>
        </div>
      </div><!-- /div row -->
    </footer><!-- /footer -->
  </div> <!-- /container -->

</div>
<!-- /#wrapper -->

<!-- jQuery -->
{!!Html::script('js/jquery.js')!!}


  <!-- Bootstrap Core JavaScript -->
{!!Html::script('js/bootstrap.min.js')!!}

  <!-- Morris Charts JavaScript -->
{!!Html::script('js/plugins/morris/raphael.min.js')!!}
{!!Html::script('js/plugins/morris/morris.min.js')!!}
{!!Html::script('js/plugins/morris/morris-data.js')!!}

</body>

</html>