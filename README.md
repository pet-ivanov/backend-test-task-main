# Выполненое тестовое задание 
Исходник задания: https://github.com/systemeio/backend-test-task
Разработчик: https://hh.ru/resume/507107a3ff0fa3b9de0039ed1f7671776d5031

## Запуск

```bash
docker-compose up --build -d
bin/console doctrine:database:create
bin/console doctrine:migrations:migrate --no-interaction
bin/console doctrine:fixtures:load --no-interaction
```

## Порядок разработки

* Склонировал стартовую сборку тестового задания
* Добавил docker контейнер с базой данных
* Установил необходимые бандлы и пакеты
* Настроил подключение БД
* Добавил новые сущности Продуктов и Купонов
* Добавил контроллер, роуты и валидацию
* Добавил фикстуры с тестовыми данными
* Реализовал основную логику описанную в ТЗ с реализацией необходимых сервисов