# План правок фронтенда — Политехническое общество

## Группа 1: CSS — отступы и адаптивность

### 1.1. Лишний отступ между меню и баннером на главной
**Файл:** `local/templates/my_template/assets/css/main.css`
**Проблема:** `banner-other` имеет `margin-top: var(--section-margin)` — создаёт лишний отступ
**Решение:** Проверить значение `--section-margin` и уменьшить для главной страницы

### 1.2. Отступ заголовка "История и крепкое сообщество" на странице "О нас"
**Файл:** `local/templates/my_template/assets/css/main.css`
**Проблема:** Якорь `#history` обрезается, заголовок секции `history` начинается сразу под шапкой
**Решение:** Добавить `padding-top: 80px` или `scroll-margin-top` для `#history`:
```css
.history {
    scroll-margin-top: 80px;
}
```

---

## Группа 2: Страница "Проекты" — visits__card

### 2.1. Сжать плашки 5 проектов в 2 колонки с текстом слева
**Файл:** `local/templates/my_template/assets/css/main.css`
**Проблема:** Сейчас карточки горизонтальные, нужно сделать компактнее для 2 колонок
**Решение:**
```css
.visits__card {
    flex-direction: column; /* текст под картинкой */
    min-height: auto;
}
.visits__image {
    max-width: 100%;
    height: 200px;
}
.visits__content {
    padding: 16px;
}
```

### 2.2. Кнопки "Все проекты" и "Поддержать"
**Файл:** `local/templates/my_template/assets/css/main.css` или `projects/index.php` / `projects_listing.php`
**Решение:** Добавить кнопки в HTML под текстом описания:
- "Все проекты" — `.btn-transparent` (белая с красной рамкой, реверс при hover)
- "Поддержать" — `.visits__btn--help` (красная)

---

## Группа 3: Главная — Всплывающие окна для членов совета

### 3.1. Fancybox окна для членов совета на главной
**Файл:** `index.php` (уже исправлен) + `local/php_interface/renderers/board_section.php`
**Решение:** Убедиться что `data-fancybox data-src="#form-boards-N"` есть на карточках и модалки рендерятся

---

## Группа 4: Индустриальное партнерство (инициативы)

### 4.1. Popup форма вместо разворачиваемого фрейма
**Файл:** `local/templates/my_template/initiatives.html` или `initiatives/index.php`
**Решение:** Кнопка "Стать партнёром" должна открывать fancybox popup, не iframe

### 4.2. Верхняя синяя плашка без отступа
**Файл:** `local/templates/my_template/assets/css/main.css`
**Проблема:** `.banner-other--initiatives` прижата к верху без padding-top
**Решение:**
```css
.banner-other--initiatives {
    padding-top: 40px; /* добавить отступ */
}
```

### 4.3. Звёздочка у политики обработки ПДн
**Файл:** `initiatives/index.php` или `local/templates/my_template/initiatives.html`
**Решение:** Добавить `*` в текст ссылки на политику

---

## Группа 5: Страница "Поддержать"

### 5.1. Выравнивание текста по ширине
**Файл:** `support/index.php`
**Решение:** Для текстовых элементов использовать `text-align: justify`

### 5.2. Линия под "Введите сумму" того же цвета
**Файл:** `local/templates/my_template/assets/css/main.css`
**Проблема:** Input border использует прозрачный цвет
**Решение:**
```css
.project-programm input,
.support input {
    border-bottom: 1px solid #003039;
}
```

### 5.3. Единый шрифт для суммы
**Файл:** `support/index.php`
**Решение:** Добавить `font-family: var(--text-geologica)` для input суммы

---

## Группа 6: Подстановка названия проекта

### 6.1. На странице "Поддержать" + детальная страница
**Файл:** `support/index.php`
**Решение:** Из URL параметра `?project=...` подставлять в value поля:
```php
$projectName = $_GET['project'] ?? '';
```
```html
<input type="text" name="project" value="<?= htmlspecialchars($projectName) ?>">
```

---

## Группа 7: Страница "Референс-визиты"

### 7.1. Якорь "#culture" с отступом
**Файл:** `reference/index.php`
**Решение:** Изменить `href="#culture"` на `href="#culture"` + добавить scroll-margin:
```css
#culture {
    scroll-margin-top: 80px;
}
```

### 7.2. Звёздочка у политики в форме "Стать принимающей стороной"
**Файл:** `reference/index.php`
**Решение:** Добавить `*` после текста политики

---

## Группа 8: Страница "Витрина компетенций"

### 8.1. Скрыть кнопку для завершённых визитов
**Файл:** `competencies/index.php`
**Решение:** Добавить проверку статуса визита:
```php
if ($visitStatus !== 'completed'):
?>
<a href="#" class="btn">Зарегистрироваться</a>
<?php endif; ?>
```

### 8.2. Popup форма "Связаться с организаторами"
**Файл:** `competencies/index.php`
**Решение:** Изменить inline-block на fancybox popup

### 8.3. Уменьшить блок в шапке
**Файл:** `local/templates/my_template/assets/css/main.css`
**Проблема:** Шапка витрины компетенций слишком высокая
**Решение:** Добавить `min-height` как у `banner-other-project`:
```css
.banner-other.competencies-banner {
    min-height: 300px;
}
```

---

## Группа 9: Страница "Карьерная платформа" (resume-form)

### 9.1. Звёздочки у всех обязательных полей
**Файл:** `resume-form/index.php` или `local/templates/my_template/resume-form.html`
**Решение:** Добавить `*` после каждого label обязательного поля

### 9.2. Звёздочка у согласия ПДн
**Файл:** `resume-form/index.php`
**Решение:** Добавить `*` после "Согласен с политикой"

---

## Группа 10: Страница "О нас"

### 10.1. Фон/отступ первого блока
**Файл:** `about/index.php` или CSS
**Решение:** Проверить `.director` и `.director__wrapper` на соответствие макету

---

## Группа 11: Попечительский совет

### 11.1. Выравнивание popup по верху
**Файл:** `local/templates/my_template/assets/css/main.css`
**Решение:**
```css
.form-boards__content {
    align-content: flex-start;
}
```

---

## Группа 12: Страница "Регистрация"

### 12.1. Поле телефона для физлиц
**Файл:** `join/index.php`
**Решение:** Добавить input телефона в форму физлиц с `required`

### 12.2. Звёздочки у даты рождения и выдачи диплома
**Файл:** `join/index.php`
**Решение:** Добавить `*` после label этих полей

---

## Группа 13: Индустриальное партнерство — звёздочки

### 13.1. Унификация звёздочек во всех формах
**Файл:** `local/templates/my_template/partnership_form_markup.php` или HTML
**Решение:** Убрать общую звёздочку у блока, оставить только у отдельных полей

---

## Группа 14: Документы

### 14.1. Политика открывается в двух окнах
**Файл:** Все HTML файлы с ссылками на политику
**Решение:** Добавить `target="_blank"` если нужно, или убрать если не нужно

---

## Приоритеты выполнения

1. **Критичные** (могут ломать UX):
   - 6.1 (подстановка проекта)
   - 2.1-2.2 (проекты)
   - 8.2 (popup компетенций)

2. **Важные** (влияют на визуал):
   - 1.1-1.2 (отступы)
   - 5.1-5.3 (поддержать)
   - 4.1-4.3 (инициативы)

3. **Мелкие** (мелочи):
   - 9.1-9.2 (карьерная)
   - 12.1-12.2 (регистрация)
   - 10.1, 11.1, 13.1
