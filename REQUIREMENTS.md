# 📝 CMS - Contento Requirements

## 1. Content Entities

### Pages

Used for standard site pages and news articles.

* `title`: (String)
* `slug`: (String) Unique identifier for URLs
* `type`: (Enum) `news` | `page`
* `active`: (Boolean)
* `visible_date_from`: (Timestamp) Visibility start
* `visible_date_to`: (Timestamp) Visibility end
* `important`: (Boolean)
* `published_at`: (Timestamp) Date of publication
* `author`: (String)
* `abstract`: (Text) Short summary
* `content`: (JSON) Main page content
* `created_by`: (User ID)
* `updated_by`: (User ID)

### FAQ Categories

Groups for organizing frequently asked questions.

* `title`: (String)
* `slug`: (String)
* `active`: (Boolean)
* `abstract`: (Text) Introductory text
* `created_by`: (User ID)
* `updated_by`: (User ID)

### FAQ

Individual question and answer pairs.

* `faq_category_id`: (Foreign Key) Links to FAQ Category
* `title`: (String)
* `slug`: (String)
* `active`: (Boolean)
* `visible_date_from`: (Timestamp)
* `visible_date_to`: (Timestamp)
* `content`: (HTML/Text) The answer content
* `created_by`: (User ID)
* `updated_by`: (User ID)

---

## 2. Interaction & Feedback

### Mail Forms

Configuration for dynamic contact forms.

* `name`: (String)
* `slug`: (String)
* `email_to`: (String)
* `email_cc`: (String)
* `email_bcc`: (String)
* `custom_fields`: (JSON)
* `redirect_url`: (String)
* `custom_data`: (Text)
* `options`: (Text)
* `newsletter`: (Boolean)
* `created_by`: (User ID)
* `updated_by`: (User ID)

### Modals / Popups

Configuration for site-wide overlays and notifications.

* `title`: (String)
* `slug`: (String)
* `active`: (Boolean)
* `visible_date_from`: (Timestamp)
* `visible_date_to`: (Timestamp)
* `template`: (Enum) `default`, `default_sm`, `default_lg`, `img_left`, `img_right`
* `content`: (HTML/Text)
* `cta_button_text`: (String)
* `cta_button_url`: (String)
* `cta_button_color`: (String)
* `image`: (String/Path)
* `timeout`: (Integer)
* `popup_time`: (Enum) `always`, `once_an_hour`, `once_a_day`, `once_a_week`, `once_a_month`, `once_a_year`
* `show_on_all_pages`: (Boolean)
* `created_by`: (User ID)
* `updated_by`: (User ID)

---

## 3. Configuration & Settings

### Settings (Key-Value Store)

* `group`: (String) Category of the setting
* `name`: (String)
* `value`: (Text)
