# yii2-import
Yii2 module for import data from files to database between activeRecord

## Installation

The preferred way to install this extension is through [composer](http://getcomposer.org/download/).

### Install

Either run

```
$ php composer.phar require execut/yii2-import "dev-master"
```

or add

```
"execut/yii2-import": "dev-master"
```

to the ```require``` section of your `composer.json` file.

### Configuration

Add module bootstrap in target web application config:
```php
    'bootstrap' => [
    ...
        'import' => [
            'class' => \execut\import\bootstrap\Backend::class,
        ]
    ...
    ],
```

Add module bootstrap in console application config:
```php
    'bootstrap' => [
    ...
        'import' => [
            'class' => \execut\import\bootstrap\Console::class,
        ]
    ...
    ],
```

Apply migrations via yii command:
```
./yii migrate/up --migrationPath=vendor/kartik-v/yii2-dynagrid/src/migrations
```

After configuration, the module should open by paths:
import/files
import/settings

### Module navigation

You may output navigation of module inside your layout via execut/yii2-navigation:
```php
    echo Nav::widget([
        ...
        'items' => \yii\helpers\ArrayHelper::merge($menuItems, \yii::$app->navigation->getMenuItems()),
        ...
    ]);
    NavBar::end();

    // Before standard breadcrumbs render breadcrumbs and header widget:
    echo \execut\navigation\widgets\Breadcrumbs::widget();
    echo \execut\navigation\widgets\Header::widget();
```
For more information about execut/yii2-navigation module, please read it [documentation](https://github.com/execut/yii2-navigation)

### Описание разделов
#### Файлы

По адресу import/files происходит управление файлами, которые импортируются. Здесь можно вручную загружать новые
и управлять загрузками. Каждому файлу можно выставить статусы и консольная команда для импорта подхватит его:
* New - загрузить файл
* Reload - перезагрузиь файл
* Delete - удалить файл
* Loaded - файл загружен
* Stop - остановить загрузку
* Error - ошибка загрузки файла

Остальные статусы используются консольной командой для отображения процесса импорта и если их выставить вручную,
поведение импорта непредсказуемо:
* Loading - файл в процессе импорта
* Stopped - импорт файла остановлен
* Deleting - файл в процессе удаления

#### Настройки

По адресу import/settings можно управлять настройками, через которые происходит захват файлов их внешних источников,
разбор файлов на данные и их запись в базу данных.

Чтобы начать производить настройки импорта, модулю необходимо указнать про ваше окружение базы данных через реализацию
плагина execut\import\Plugin. Подробнее о том как реализовать этот плагин, смотрите раздел
[создание плагинов](#создание-плагинов)

#### Консоль

В консоли есть 3 команды:
##### import
Команда ./yii import запускает процесс импорта и удаления файлов. Этот процесс пошагово выглядит так:
1. Отбирается самый старый по дате создания файл со статусом New и Reload
2. Удаляется старый файл с такой-же настройкой как и новый
3. Происходит его парсинг и запись в БД
4. Шаг 1 повторяется

Эта команда поддерживает её параллельный запуск для большей скорости импорта.
У команды есть единственный необязательный аргумент: идентификатор файла, который необходимо импортировать. Если его
передать, то указанный файл начнёт импортироваться через шаги 1-3. После выполнения шага 3 процесс выполнения команды
обрывается

##### import/check-source
./yii import/check-source выполняет процесс захвата файлов из внешних источников через настройки и их запись в базу для
последующего запуска.
Аргументы команды:
* type - тип источника. Может быть email, ftp или site.
* id - идентификатор настройки. Если указать его, то произойдёт захват только указанной настройки.

Команда поддерживает параллельный запуск.

##### import/release-trigger
./yii import/release-trigger очищает все mutex-триггеры. Применяется для случаев, если произошёл сбой при выполнении
команд для разблокировки их дальнейшего выполнения.


#### Создание плагинов

Для изучения принципа создания плагина, рассмотрим простой пример. У нас есть товар. У товара есть название и цена.
Нам нужно импортировать этот товар в базу данных каталога.