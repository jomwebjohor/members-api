## Installation

* `git clone https://github.com/jomwebjohor/members-api.git member`
* `cd member`
* `composer install`

## Database
* SQLite

## Steps
* Head to `http://localhost/member/jwj` to request token
* Sends the token in Authorization header

## API
* POST `/api/members`
	* `name` is required
	* `location` is required
	* `position` is optional
	* `company` is optional
	* `skills` is optional, comma separated e.g PHP,Ruby,Python
	* `facebook` is optional
	* `twitter` is optional
	* `github` is optional
	* `telegram` is optional
* PUT `/api/members/{id}`
	* `name` is optional
	* `location` is optional
	* `position` is optional
	* `company` is optional
	* `skills` is optional
	* `facebook` is optional
	* `twitter` is optional
	* `github` is optional
	* `telegram` is optional
* GET `/api/members/{username}`
  	* `username` will try to search from member name, facebook, twitter, github & telegram username

## Responses
* 200 OK

```json
{
    "name": "Salahuddin Hairai",
    "location": "Kg Melayu Majidee, JB",
    "position": "Software Engineer",
    "company": "Financio Sdn Bhd",
    "skills": [
        "PHP",
        "Laravel",
        "Phalcon",
        "Symfony",
        "CodeIgniter"
    ],
    "social": {
        "facebook": {
            "username": "salahuddin.hairai",
            "uri": "https://facebook.com/salahuddin.hairai"
        },
        "twitter": {
            "username": "od3n",
            "uri": "https://twitter.com/od3n"
        },
        "github": {
            "username": "od3n",
            "uri": "https://github.com/od3n"
        },
        "telegram": "od3n87"
    },
    "error": false,
    "status": 200
}
```

* 400 Bad Request

````json
{
  "error": true,
  "msg": "Invalid name.",
  "status": 400
}
````

```json
{
  "error": true,
  "msg": "Error occured while trying to save the record.",
  "status": 400
}
```

* 401 Unauthorized

`null`