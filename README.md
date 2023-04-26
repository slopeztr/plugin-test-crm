# Hometech CRM

## Requisitos de ambiente

[WordPress v.6.1.1+](https://es-co.wordpress.org/download/#download-install)

[PHP v.8.0+](https://linuxize.com/post/how-to-install-php-8-on-ubuntu-20-04/#installing-php-80-with-nginx)

[NGINX v.1.16.0+](https://ubuntu.com/tutorials/install-and-configure-nginx#2-installing-nginx)

[MySQL v8.0+](https://dev.mysql.com/downloads/mysql/)

## Requisitos de complementos

Advanced Custom Fields PRO v6.0.7+

[ACF User Role Field Setting v4.0.2+](https://es-co.wordpress.org/plugins/user-role-field-setting-for-acf/)

[JWT Auth v2.1.3+](https://wordpress.org/plugins/jwt-auth/) (únicamente si trabaja con el API REST de WordPress)

## Ambiente de desarrollo

[Install Local by Flywheel](https://localwp.com/) (opcional)

[Guía de importación de Wordpress a Local by Flywheel ](https://localwp.com/help-docs/getting-started/how-to-import-a-wordpress-site-into-local/)

## Configuración adicional WP-CONFIG

```
/* Control de logs */
define( 'WP_DEBUG', true );
define( 'WP_DEBUG_LOG', true );

/* No almacenar ninguna revisión (excepto el autoguardado por publicación) */
define( 'WP_POST_REVISIONS', false );
```

## Configuración adicional JWT Auth

```/* Constantes del complemento JWT para el uso de tokens */
define( 'JWT_AUTH_SECRET_KEY', 'ANY_KEY' );
define( 'JWT_AUTH_CORS_ENABLE', true );
```