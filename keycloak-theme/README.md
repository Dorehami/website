# Keycloak Login Theme for Dorehami

This folder contains a simple Keycloak theme that mimics the login page of the Dorehami website. Copy the `dorehami` directory into the `themes` directory of your Keycloak installation (e.g. `/opt/keycloak/themes`).

After copying, enable the theme in the Keycloak admin console under **Realm Settings → Themes → Login Theme**.

## Updating CSS

The login template expects a compiled `app.css` file inside the theme's `resources` directory. When you build the main website, copy the generated file from `public/build` (it will look like `app.<hash>.css`) to `themes/dorehami/resources/app.css` on the Keycloak server. Update this file whenever the website assets are rebuilt so the theme stays in sync.
