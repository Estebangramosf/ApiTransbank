<!DOCTYPE html>
<html>
    <head>
        <title>ApiTransbank</title>

        <link href="https://fonts.googleapis.com/css?family=Lato:100" rel="stylesheet" type="text/css">
        <!-- Latest compiled and minified CSS -->
        <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/css/bootstrap.min.css" integrity="sha384-BVYiiSIFeK1dGmJRAkycuHAHRg32OmUcww7on3RYdg4Va+PmSTsz/K68vbdEjh4u" crossorigin="anonymous">

        <!-- Optional theme -->
        <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/css/bootstrap-theme.min.css" integrity="sha384-rHyoN1iRsVXV4nD0JutlnGaslCJuC7uwjduW9SVrLvRYooPp2bWYgmgJQIXwl/Sp" crossorigin="anonymous">

        <!-- Latest compiled and minified JavaScript -->
        <script src="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/js/bootstrap.min.js" integrity="sha384-Tc5IQib027qvyjSMfHjOMaLkfuWVxZxUPnCJA7l2mCWNIpG9mGCD8wGNIcPD7Txa" crossorigin="anonymous"></script>
        <style>
            html, body {
                height: 100%;
            }

            body {
                margin: 0;
                padding: 0;
                width: 100%;
                display: table;
                font-weight: 200;
                font-family: 'Lato';
            }

            .container {
                text-align: center;
                display: table-cell;
                vertical-align: middle;
            }

            .content {
                text-align: center;
                display: inline-block;
            }

            .title {
                font-size: 96px;
            }

            .sub-title {
                font-size: 26px;
            }
        </style>
    </head>
    <body>
        <div class="container">
            <div class="content">
                <div class="title">ApiTransbank</div>

                <div class="sub-title">
                    Métodos de la Api <br>

                    <div class="well">
                        <strong>Route::</strong><i style="color:orange;">get</i><b>(</b>'<a href="/webpaynormal">webpaynormal</a>', 'WebpayController@index'<b>);</b><br>
                    </div>

                    <div class="well">
                        <strong>Route::</strong><i style="color:orange;">post</i><b>(</b>'<a href="/getResult">getResult</a>', 'WebpayController@getResult'<b>);</b><br>
                    </div>

                    <div class="well">
                        <strong>Route::</strong><i style="color:orange;">post</i><b>(</b>'<a href="/end">end</a>', 'WebpayController@end'<b>);</b><br>
                    </div>

                    <div class="well">
                        <strong>Route::</strong><i style="color:orange;">post</i><b>(</b>'<a href="getShoppingCart">getShoppingCart</a>','WebpayController@getShoppingCart'<b>);</b><br>
                    </div>
                </div>

            </div>
        </div>
    </body>
</html>
