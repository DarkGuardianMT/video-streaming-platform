<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href= "../css/style.css">

    <title>NETFISH</title>
</head>
<body>

   <header>
        <nav>
            <ul>
                <li id="navnaam">
                    <a href="index.php" class="logo">Netfish</a>
                </li>
            </ul>
            
            <ul>
                <li id="login">
                    <a href="login.php">Login</a>
                </li>
                <li>
                    <a href="admin">Admin</a>
                </li>
            </ul>
        </nav>
    </header>


    <div id="zoeken">

    <h2>Welkom bij NetFish!</h2>
    <p>Bekijk onze nieuwste video`s</p>

    <form class="search" action="videos.php" method="get">
        <input type="search" name="search" id="search" placeholder="Zoek videos..."/>
        <button type="submit" id="zoekButton">Zoek</button>
    </form>

    





    <script src= "../js/script.js">

    
</body>
</html>