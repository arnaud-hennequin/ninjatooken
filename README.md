<div id="top"></div>

[![Contributors][contributors-shield]][contributors-url]
[![Forks][forks-shield]][forks-url]
[![Stargazers][stars-shield]][stars-url]
[![Issues][issues-shield]][issues-url]
[![MIT License][license-shield]][license-url]
[![LinkedIn][linkedin-shield]][linkedin-url]

<br />
<div align="center">
  <a href="http://ninjatooken.fr/en/">
    <img src="public/images/logo.png" alt="Logo" width="199" height="118">
  </a>

<h3 align="center">NinjaTooken</h3>

  <p align="center">
    Code source of NinjaTooken's Website
    <br />
    <a href="https://github.com/arnaud-hennequin/ninjatooken"><strong>Explore the docs »</strong></a>
    <br />
    <br />
    <a href="http://ninjatooken.fr/en/">View Website</a>
    ·
    <a href="https://github.com/arnaud-hennequin/ninjatooken/issues">Report Bug</a>
    ·
    <a href="https://github.com/arnaud-hennequin/ninjatooken/issues">Request Feature</a>
  </p>
</div>



<!-- TABLE OF CONTENTS -->
<details>
  <summary>Table of Contents</summary>
  <ol>
    <li>
      <a href="#about-the-project">About The Project</a>
      <ul>
        <li><a href="#built-with">Built With</a></li>
      </ul>
    </li>
    <li>
      <a href="#getting-started">Getting Started</a>
      <ul>
        <li><a href="#prerequisites">Prerequisites</a></li>
        <li><a href="#installation">Installation</a></li>
      </ul>
    </li>
    <li><a href="#roadmap">Roadmap</a></li>
    <li><a href="#contributing">Contributing</a></li>
    <li><a href="#license">License</a></li>
    <li><a href="#contact">Contact</a></li>
  </ol>
</details>



<!-- ABOUT THE PROJECT -->
## About The Project

**Ninja Tooken is a free network game!**

Discover a world where ninjas are merciless. Suiton, Futon, Raiton, Doton, Katon... Each class have their specific powers, so choose yours carefully!

Create your account to save your experience, join a clan, or participate in tournaments.

Source code of the game (made with Unity 3D) is kept at https://gitlab.com/arnaud-hennequin/ninjatooken

<p align="right">(<a href="#top">back to top</a>)</p>



### Built With

* [Symfony 6.4](https://symfony.com/)
* [Sonata Project](https://sonata-project.org/)
* [LiipImagineBundle](https://github.com/liip/LiipImagineBundle)
* [Imgur API](https://api.imgur.com/)
* [HTMLPurifier](https://github.com/Exercise/HTMLPurifier)
* [MySQLDump - PHP](https://github.com/ifsnop/mysqldump-php)
* [Doctrine Behaviors](https://github.com/KnpLabs/DoctrineBehaviors) / Sluggable
* [Font Awesome](https://fontawesome.com/)
* [Bootstrap](https://getbootstrap.com)
* [JQuery](https://jquery.com)
* [{less}](https://lesscss.org/)
* [TinyMCE](https://www.tiny.cloud/tinymce/)
* [MotionCAPTCHA](https://github.com/josscrowcroft/MotionCAPTCHA)
* [Discord html-embed](https://github.com/widgetbot-io/html-embed)

<p align="right">(<a href="#top">back to top</a>)</p>



<!-- GETTING STARTED -->
## Getting Started

For getting started, you just have to follow main symfony install steps.

### Prerequisites

You will need at least, a server with PHP / Mysql / Nginx installed.
```shell
sudo apt update
```
* nginx (>= 1.1)
```shell
sudo apt install nginx
```
* mysql (>= 5)
```shell
sudo apt install mysql-server
sudo mysql_secure_installation
```
* php (>= 8.1)
```shell
sudo apt install php8.1 php8.1-{fpm,mysql,zip,intl,gd,cli,bz2,curl,mbstring,pgsql,opcache,soap,cgi}
```

### Installation

1. Clone the repo
```shell
git clone https://github.com/arnaud-hennequin/ninjatooken.git
```
2. Then, open a new configuration file in Nginx’s sites-available directory using your preferred command-line editor. Here, we’ll use nano:
```shell
sudo nano /etc/nginx/sites-available/ninjatooken
```
And paste in the [following configuration](docker/nginx/default.conf) (adapt `fastcgi_pass` to your need), and then...
```shell
sudo ln -s /etc/nginx/sites-available/ninjatooken /etc/nginx/sites-enabled/
sudo service nginx reload
```
3. Copy .env.test to .env and edit parameters to fit your server's installation.
4. [Install composer](https://getcomposer.org/doc/00-intro.md#installation-linux-unix-macos)
5. Download the vendors
 ```shell
php composer.phar install
 ```
6. If the database you have entered during step 2 doesn't exist yet, create it :
```shell
php bin/console doctrine:database:create
```
And then create corresponding tables :
```shell
php bin/console doctrine:schema:update --dump-sql
php bin/console doctrine:schema:update --force
```
7. Install the assets in the public folder
```shell
php bin/console cache:clear
php bin/console assets:install
```

Add **127.0.0.1 ninjatooken.test** in your host file, and access the site via http://ninjatooken.test !


Also, please take note that an automatic mysql backup can be tasked:
```shell
crontab -e
```
And then paste (every day at 4am):
```text
0 4 * * * php /var/www/ninjatooken/bin/console doctrine:database:backup
```

<p align="right">(<a href="#top">back to top</a>)</p>


<!-- ROADMAP -->
## Roadmap

See the [website's forum](http://ninjatooken.fr/fr/forum/ameliorations-propositions-d-idees) and [open issues](https://github.com/arnaud-hennequin/ninjatooken/issues) for a full list of proposed features (and known issues).

<p align="right">(<a href="#top">back to top</a>)</p>



<!-- CONTRIBUTING -->
## Contributing

Contributions are what make the open source community such an amazing place to learn, inspire, and create. Any contributions you make are **greatly appreciated**.

If you have a suggestion that would make this better, please fork the repo and create a pull request. You can also simply open an issue with the tag "enhancement".
Don't forget to give the project a star! Thanks again!

1. Fork the Project
2. Create your Feature Branch (`git checkout -b feature/AmazingFeature`)
3. Commit your Changes (`git commit -m 'Add some AmazingFeature'`)
4. Push to the Branch (`git push origin feature/AmazingFeature`)
5. Open a Pull Request

<p align="right">(<a href="#top">back to top</a>)</p>



<!-- LICENSE -->
## License

Distributed under the MIT License. See `LICENSE.txt` for more information.

<p align="right">(<a href="#top">back to top</a>)</p>



<!-- CONTACT -->
## Contact

Arnaud Hennequin - (http://ninjatooken.fr/en/nous-contacter) - arhennequin@gmail.com

Project Link: [https://github.com/arnaud-hennequin/ninjatooken](https://github.com/arnaud-hennequin/ninjatooken)

<p align="right">(<a href="#top">back to top</a>)</p>


[contributors-shield]: https://img.shields.io/github/contributors/arnaud-hennequin/ninjatooken.svg?style=for-the-badge
[contributors-url]: https://github.com/arnaud-hennequin/ninjatooken/graphs/contributors
[forks-shield]: https://img.shields.io/github/forks/arnaud-hennequin/ninjatooken.svg?style=for-the-badge
[forks-url]: https://github.com/arnaud-hennequin/ninjatooken/network/members
[stars-shield]: https://img.shields.io/github/stars/arnaud-hennequin/ninjatooken.svg?style=for-the-badge
[stars-url]: https://github.com/arnaud-hennequin/ninjatooken/stargazers
[issues-shield]: https://img.shields.io/github/issues/arnaud-hennequin/ninjatooken.svg?style=for-the-badge
[issues-url]: https://github.com/arnaud-hennequin/ninjatooken/issues
[license-shield]: https://img.shields.io/github/license/arnaud-hennequin/ninjatooken.svg?style=for-the-badge
[license-url]: https://github.com/arnaud-hennequin/ninjatooken/blob/master/LICENSE.txt
[linkedin-shield]: https://img.shields.io/badge/-LinkedIn-black.svg?style=for-the-badge&logo=linkedin&colorB=555
[linkedin-url]: https://linkedin.com/in/arnaud-hennequin
