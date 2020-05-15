<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">

        <title>Road List</title>

        <!-- Fonts -->
        <link href="https://fonts.googleapis.com/css?family=Nunito:200,600" rel="stylesheet">

        <!-- Styles -->
        <style>
            html, body {
                background-color: #fff;
                color: #636b6f;
                font-family: 'Nunito', sans-serif;
                font-weight: 200;
                height: 100vh;
                margin: 0;
            }

            .full-height {
                height: 100vh;
            }

            .flex-center {
                align-items: center;
                display: flex;
                justify-content: center;
            }

            .position-ref {
                position: relative;
            }

            .top-right {
                position: absolute;
                right: 10px;
                top: 18px;
            }

            .content {
                text-align: center;
            }

            .title {
                font-size: 84px;
            }

            .links > a {
                color: #636b6f;
                padding: 0 25px;
                font-size: 13px;
                font-weight: 600;
                letter-spacing: .1rem;
                text-decoration: none;
                text-transform: uppercase;
            }

            .m-b-md {
                margin-bottom: 30px;
            }

            #getRoadList {
                width: 400px;
            }

            #getRoadList div{
                float: right;
            }

            #getRoadList button, #getRoadList svg{
                margin: 20px;
            }
        </style>
    </head>
    <body>
        <div class="flex-center position-ref full-height">
            @if (Route::has('login'))
                <div class="top-right links">
                    @auth
                        <a href="{{ url('/home') }}">Home</a>
                    @else
                        <a href="{{ route('login') }}">Login</a>

                        @if (Route::has('register'))
                            <a href="{{ route('register') }}">Register</a>
                        @endif
                    @endauth
                </div>
            @endif

            <div class="content">
                <div class="title m-b-md">
                    Road List
                </div>

                <form id="getRoadList" enctype="multipart/form-data">
                    <div>
                        <span>Организация: </span>
                        <input name="organization" id="organization" type="text" placeholder="ООО ТестСтрой" required>
                    </div>
                    <div>
                        <span>Первый заказчик: </span>
                        <input name="first_customer" id="first_customer" type="text" placeholder="Бисков А.А." required>
                    </div>
                    <div>
                        <span>Начальный километраж: </span>
                        <input name="first_mileage" id="first_mileage" type="text" placeholder="174000" required>
                    </div>
                    <div>
                        <span>Расчёт цены: </span>
                        <select style="width: 173px;" name="price_type" id="price_type">
                            <option value="cr">Кл-во поездок</option>
                            <option value="cube">Кубы</option>
                        </select>
                    </div>
                    <div>
                        <span>Цена: </span>
                        <input name="price" id="price" type="text" placeholder="600000" required>
                    </div>
                    <div>
                        <span>Таксировщик: </span>
                        <input name="tax_user" id="tax_user" type="text" placeholder="Бисков А.А." required>
                    </div>
                    <div>
                        <span>Начальник эксплуатации: </span>
                        <input name="exp_user" id="exp_user" type="text" placeholder="Бисков А.А." required>
                    </div>
                    <div>
                        <span>Загрузите реестр: </span>
                        <input name="register" id="register" type="file" accept="application/vnd.openxmlformats-officedocument.spreadsheetml.sheet" required>
                    </div>
                    <button id="btn_submit" type="submit" class="btn">Получить путевые листы</button>
                    <svg id="preloader" style="display: none;" xmlns:svg="http://www.w3.org/2000/svg" xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" version="1.0" width="158px" height="24px" viewBox="0 0 158 24" xml:space="preserve"><rect x="0" y="0" width="100%" height="100%" fill="#FFFFFF" /><path fill="#e3e3e3" fill-opacity="0.11" d="M64 4h10v10H64V4zm20 0h10v10H84V4zm20 0h10v10h-10V4zm20 0h10v10h-10V4zm20 0h10v10h-10V4zM4 4h10v10H4V4zm20 0h10v10H24V4zm20 0h10v10H44V4z"/><path fill="#bdbdbd" fill-opacity="0.26" d="M144 14V4h10v10h-10zm9-9h-8v8h8V5zm-29 9V4h10v10h-10zm9-9h-8v8h8V5zm-29 9V4h10v10h-10zm9-9h-8v8h8V5zm-29 9V4h10v10H84zm9-9h-8v8h8V5zm-29 9V4h10v10H64zm9-9h-8v8h8V5zm-29 9V4h10v10H44zm9-9h-8v8h8V5zm-29 9V4h10v10H24zm9-9h-8v8h8V5zM4 14V4h10v10H4zm9-9H5v8h8V5z"/><g><path fill="#d9d9d9" fill-opacity="0.15" d="M-58 16V2h14v14h-14zm13-13h-12v12h12V3z"/><path fill="#9c9c9c" fill-opacity="0.39"  d="M-40 0h18v18h-18z"/><path fill="#b2b2b2" fill-opacity="0.3" d="M-40 18V0h18v18h-18zm17-17h-16v16h16V1z"/><path fill="#9c9c9c" fill-opacity="0.39"  d="M-20 0h18v18h-18z"/><path fill="#4c4c4c" fill-opacity="0.7" d="M-20 18V0h18v18h-18zM-3 1h-16v16h16V1z"/><animateTransform attributeName="transform" type="translate" values="20 0;40 0;60 0;80 0;100 0;120 0;140 0;160 0;180 0;200 0" calcMode="discrete" dur="1800ms" repeatCount="indefinite"/></g></svg>
                </form>
            </div>
        </div>
    </body>

    <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.5.1/jquery.min.js" crossorigin="anonymous"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/axios/0.19.2/axios.min.js" crossorigin="anonymous"></script>
    <script src="{{ asset('js/main.js') }}" crossorigin="anonymous"></script>
</html>
