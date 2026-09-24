import json
import os

filepath = 'breskul_api_collection.json'
with open(filepath, 'r') as f:
    data = json.load(f)

for folder in data.get('item', []):
    if folder.get('name') == 'Public':
        for req in folder.get('item', []):
            if req.get('name') == 'Login':
                req['event'] = [
                    {
                        'listen': 'test',
                        'script': {
                            'exec': [
                                "var jsonData = pm.response.json();",
                                "var token = jsonData.token || (jsonData.data && jsonData.data.token);",
                                "if (token) {",
                                "    pm.collectionVariables.set('token', token);",
                                "    console.log('Token automatically set to collection variables');",
                                "}"
                            ],
                            'type': 'text/javascript'
                        }
                    }
                ]
                req['request']['body'] = {
                    'mode': 'raw',
                    'raw': '{\n    "login": "email@example.com",\n    "password": "password"\n}',
                    'options': {
                        'raw': {
                            'language': 'json'
                        }
                    }
                }
            elif req.get('name') == 'Refresh Token':
                req['event'] = [
                    {
                        'listen': 'test',
                        'script': {
                            'exec': [
                                "var jsonData = pm.response.json();",
                                "var token = jsonData.token || (jsonData.data && jsonData.data.token);",
                                "if (token) {",
                                "    pm.collectionVariables.set('token', token);",
                                "    console.log('Token automatically refreshed');",
                                "}"
                            ],
                            'type': 'text/javascript'
                        }
                    }
                ]

# Pre-request script to handle something if needed, but Postman natively handles Bearer auth properly if set up.
# The collection variable `token` is already linked in the Protected folder auth.

with open(filepath, 'w') as f:
    json.dump(data, f, indent=4)
print('Collection updated successfully.')
