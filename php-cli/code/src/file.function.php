<?php
function readAllFunction(array $config): string
{
    $address = $config['storage']['address'];

    if (file_exists($address) && is_readable($address)) {
        $file = fopen($address, "rb");

        $contents = '';

        while (!feof($file)) {
            $contents .= fread($file, 100);
        }

        fclose($file);
        return $contents;
    } else {
        return handleError("Файл не существует");
    }
}

function addFunction(array $config): string
{
    $address = $config['storage']['address'];

    $name = readline("Введите имя: ");

    if (!validateName($name)) {
        return handleError("Некорректный формат имени!");
    }
    $date = readline("Введите дату рождения в формате ДД-ММ-ГГГГ: ");

    if (!validateDate($date)) {
        return handleError("Некорректный формат даты!");
    }

    $data = $name . ", " . $date . "\r\n";

    $fileHandler = fopen($address, 'a');

    if (fwrite($fileHandler, $data)) {
        fclose($fileHandler);
        return "Запись $data добавлена в файл $address";
    } else {
        fclose($fileHandler);
        return handleError("Произошла ошибка записи. Данные не сохранены");
    }
}

function clearFunction(array $config): string
{
    $address = $config['storage']['address'];

    if (file_exists($address) && is_readable($address)) {
        $file = fopen($address, "w");

        fwrite($file, '');

        fclose($file);
        return "Файл очищен";
    } else {
        return handleError("Файл не существует");
    }
}

function helpFunction(): string
{
    return handleHelp();
}

function readConfig(string $configAddress): array|false
{
    return parse_ini_file($configAddress, true);
}

function findBirthdayPerson(array $config): string
{
    $thisDay = date('d');
    $thisMonth = date('m');
    $address = $config['storage']['address'];
    $result = '';

    if (file_exists($address) && is_readable($address)) {
        $file = fopen($address, "r");

        while (!feof($file)) {
            $line = fgets($file);
            if (!$line) break;
            $blocks = explode(", ", $line);
            $birthday = substr(trim($blocks[1]), 0, -5);
            $birthArray = explode('-', $birthday);
            if ($thisDay === $birthArray[0] && $thisMonth === $birthArray[1]) {
                $result .= $blocks[0] . PHP_EOL;
            }
        }
        fclose($file);
        if (strlen($result) === 0) {
            return "На сегодня именинников нет!";
        }
        return $result;
    } else {
        return handleError("Файл не существует");
    }
}

function deleteUser(array $config): string
{
    $address = $config['storage']['address'];

    if (file_exists($address) && is_readable($address)) {
        echo "Для удаления пользователя можно ввести его имя или дату рождения." . PHP_EOL .
            "Если выбрали имя, нажмите 1." . PHP_EOL . "Для удаления по дате нажмите 2." . PHP_EOL .
            "Передумали - нажмите что-то другое: ";
        $choice = readline();
        switch ($choice) {
            case 1:
                $name = trim(mb_strtolower(readline("Введите имя пользователя: ")));
                $file = fopen($address, 'r');
                $index = 0;
                while (!feof($file)) {
                    $line = fgets($file);
                    if (!$line) break;
                    $blocks = explode(", ", $line);
                    $nameInFile = trim(mb_strtolower($blocks[0]));
                    if ($name === $nameInFile) {
                        return "Пользователь " . rewriteFileWithoutLine($index, $address) . " найден и удален";
                    }
                    $index++;
                }
                fclose($file);
                return "Строка с таким пользователем не найдена";
            case 2:
                $date = readline("Введите дату рождения пользователя в формате ДД-ММ-ГГГГ: ");
                if (!validateDate($date)) {
                    return "Дату рождения ввели некорректно";
                }
                $file = fopen($address, 'r');
                $index = 0;
                while (!feof($file)) {
                    $line = fgets($file);
                    if (!$line) break;
                    $blocks = explode(", ", $line);
                    $dateInFile = trim($blocks[1]);
                    if ($date === $dateInFile) {
                        return "Пользователь " . rewriteFileWithoutLine($index, $address) . " найден и удален";
                    }
                    $index++;
                }
                fclose($file);
                return "Строка с таким пользователем не найдена";

            default:
                return "Хорошо, можете удалить пользователя в другой раз";
        }
    } else {
        return handleError("Файл не существует");
    }
}

function readProfilesDirectory(array $config): string
{
    $profilesDirectoryAddress = $config['profiles']['address'];

    if (!is_dir($profilesDirectoryAddress)) {
        mkdir($profilesDirectoryAddress);
    }

    $files = scandir($profilesDirectoryAddress);

    $result = "";

    if (count($files) > 2) {
        foreach ($files as $file) {
            if (in_array($file, ['.', '..']))
                continue;

            $result .= $file . "\r\n";
        }
    } else {
        $result .= "Директория пуста \r\n";
    }

    return $result;
}

function readProfile(array $config): string
{
    $profilesDirectoryAddress = $config['profiles']['address'];

    if (!isset($_SERVER['argv'][2])) {
        return handleError("Не указан файл профиля");
    }

    $profileFileName = $profilesDirectoryAddress . $_SERVER['argv'][2] . ".json";

    if (!file_exists($profileFileName)) {
        return handleError("Файл $profileFileName не существует");
    }

    $contentJson = file_get_contents($profileFileName);
    $contentArray = json_decode($contentJson, true);

    $info = "Имя: " . $contentArray['name'] . "\r\n";
    $info .= "Фамилия: " . $contentArray['lastname'] . "\r\n";

    return $info;
}