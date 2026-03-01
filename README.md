# SymfoPop - Mercat de Segona Mà

Aplicació web de mercat de segona mà on els usuaris poden registrar-se, publicar productes, editar-los i eliminar-los de manera segura. Aquest projecte inclou autenticació, base de dades amb Doctrine (fixtures i faker) i un disseny responsive amb Bootstrap 5 i Twig.

## 🛠️ Instruccions d'Instal·lació

Per posar en marxa aquest projecte de Symfony al teu equip local, segueix detalladament els següents passos:

1. **Clona el repositori:**

    ```bash
    git clone https://github.com/alexnavarro1/SymfoPop.git
    cd SymfoPop
    ```

2. **Instal·la les dependències:**
   Necessites [Composer](https://getcomposer.org/) instal·lat al teu sistema.

    ```bash
    composer install
    ```

3. **Configuració de l'Entorn (.env):**
   Duplica l'arxiu de protecció i configuració per establir un ambient al teu entorn personalitzat:

    ```bash
    cp .env.example .env
    ```

    _Assegura't de modificar el `DATABASE_URL` al teu fitxer `.env` recentment creat amb les credencials del teu gestor de bases de dades o XAMPP locals._

4. **Crear i preparar la Base de Dades (MariaDB / MySQL):**
   Utilitzem Doctrine per bolcar la construcció a la base de dades sense codi SQL manual.

    ```bash
    php bin/console doctrine:database:create
    php bin/console make:migration
    php bin/console doctrine:migrations:migrate
    ```

5. **Carregar les dades de prova (Fixtures):**
   Un cop està buit tenim dades de Faker fetes a mida per poblar el catàleg i comprovar que funciona.

    ```bash
    php bin/console doctrine:fixtures:load
    ```

    _(Escriu "yes" per confirmar en cas de demanar-ho)._

6. **Inicia el Servidor de Desenvolupament:**
   Ja ho tens tot llest! Activa'l localment a la consola.
    ```bash
    symfony serve
    # En cas de no tenir symfony-cli utilitza: php -S localhost:8000 -t public
    ```
    Obre [http://localhost:8000](http://localhost:8000) al teu navegador.

---

## 📎 Enllaços GitHub directes

A continuació pots trobar els enllaços directes a les seccions més rellevants del codi font d'aquesta aplicació elaborada en Symfony, on el codi està plenament comentat en català explicant funcions, validacions i entitats:

- [📁 Entitats](https://github.com/alexnavarro1/SymfoPop/tree/main/src/Entity)
- [🎮 Controladors](https://github.com/alexnavarro1/SymfoPop/tree/main/src/Controller)
- [📝 Formularis](https://github.com/alexnavarro1/SymfoPop/tree/main/src/Form)
- [🎨 Vistes](https://github.com/alexnavarro1/SymfoPop/tree/main/templates)
- [🔒 Seguretat](https://github.com/alexnavarro1/SymfoPop/blob/main/config/packages/security.yaml)
- [📦 Fixtures](https://github.com/alexnavarro1/SymfoPop/tree/main/src/DataFixtures)
