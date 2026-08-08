<?php

use Migrations\AbstractSeed;

class MenuLanguagesSeeder extends AbstractSeed
{
    public function run(): void
    {
        $data = [
            0 =>
                [
                    'id' => 2,
                    'string_name' => 'Project',
                    'en' => 'Project',
                    'spa' => 'Proyecto',
                    'por' => 'Projeto',
                    'deu' => 'Projekt',
                    'fra' => 'Projet',
                ],
            1 =>
                [
                    'id' => 3,
                    'string_name' => 'Archive',
                    'en' => 'Archive',
                    'spa' => 'Archivo',
                    'por' => 'Arquivo',
                    'deu' => 'Archiv',
                    'fra' => 'Archiver',
                ],
            2 =>
                [
                    'id' => 4,
                    'string_name' => 'Gantt Chart',
                    'en' => 'Gantt Chart',
                    'spa' => 'Gr├ífico de gantt',
                    'por' => 'Gr├ífico de Gantt',
                    'deu' => 'Gantt-Diagramm',
                    'fra' => 'Diagramme de Gantt',
                ],
            3 =>
                [
                    'id' => 5,
                    'string_name' => 'Activities',
                    'en' => 'Activities',
                    'spa' => 'Ocupaciones',
                    'por' => 'actividades',
                    'deu' => 'Aktivit├ñten',
                    'fra' => 'Activit├®s',
                ],
            4 =>
                [
                    'id' => 6,
                    'string_name' => 'Calendar',
                    'en' => 'Calendar',
                    'spa' => 'Calendario',
                    'por' => 'Calend├írio',
                    'deu' => 'Kalender',
                    'fra' => 'Calendrier',
                ],
            5 =>
                [
                    'id' => 7,
                    'string_name' => 'My Company',
                    'en' => 'My Company',
                    'spa' => 'Mi empresa',
                    'por' => 'Minha compania',
                    'deu' => 'Meine Firma',
                    'fra' => 'Mon entreprise',
                ],
            6 =>
                [
                    'id' => 8,
                    'string_name' => 'User',
                    'en' => 'User',
                    'spa' => 'Usuario',
                    'por' => 'Do utilizador',
                    'deu' => 'Nutzer',
                    'fra' => 'Utilisateur',
                ],
            7 =>
                [
                    'id' => 9,
                    'string_name' => 'Daily catch-Up',
                    'en' => 'Daily catch-Up',
                    'spa' => 'Ponerse al d├¡a',
                    'por' => 'Captura di├íria',
                    'deu' => 'T├ñgliches Aufholen',
                    'fra' => 'Rattrapage quotidien',
                ],
            8 =>
                [
                    'id' => 10,
                    'string_name' => 'Task',
                    'en' => 'Task',
                    'spa' => 'Tarea',
                    'por' => 'Tarefa',
                    'deu' => 'Aufgabe',
                    'fra' => 'T├óche',
                ],
            9 =>
                [
                    'id' => 11,
                    'string_name' => 'Task Group',
                    'en' => 'Task Group',
                    'spa' => 'Tarea grupal',
                    'por' => 'Grupo de Tarefas',
                    'deu' => 'Aufgabengruppe',
                    'fra' => 'Groupe de travail',
                ],
            10 =>
                [
                    'id' => 12,
                    'string_name' => 'Import & Export',
                    'en' => 'Import & Export',
                    'spa' => 'Importaci├│n y exportaci├│n',
                    'por' => 'Importa├º├úo e Exporta├º├úo',
                    'deu' => 'Import Export',
                    'fra' => 'Import & Export',
                ],
            11 =>
                [
                    'id' => 13,
                    'string_name' => 'Time Entry',
                    'en' => 'Time Entry',
                    'spa' => 'Entrada de tiempo',
                    'por' => 'Entrada de tempo',
                    'deu' => 'Zeiteintrag',
                    'fra' => 'Entr├®e dans le temps',
                ],
            12 =>
                [
                    'id' => 14,
                    'string_name' => 'Task Type',
                    'en' => 'Task Type',
                    'spa' => 'Tipo de tarea',
                    'por' => 'Tipo de Tarefa',
                    'deu' => 'Aufgabentyp',
                    'fra' => 'Type de t├óche',
                ],
            13 =>
                [
                    'id' => 15,
                    'string_name' => 'Start Timer',
                    'en' => 'Start Timer',
                    'spa' => 'Temporizador de inicio',
                    'por' => 'Iniciar temporizador',
                    'deu' => 'Timer starten',
                    'fra' => 'D├®marrer la minuterie',
                ],
            14 =>
                [
                    'id' => 16,
                    'string_name' => 'Invoice',
                    'en' => 'Invoice',
                    'spa' => 'Factura',
                    'por' => 'Fatura',
                    'deu' => 'Rechnung',
                    'fra' => 'Facture d\'achat',
                ],
            15 =>
                [
                    'id' => 17,
                    'string_name' => 'Hours Spent',
                    'en' => 'Hours Spent',
                    'spa' => 'Horas Pasadas',
                    'por' => 'Horas gastas',
                    'deu' => 'Stunden verbracht',
                    'fra' => 'Heures pass├®es',
                ],
            16 =>
                [
                    'id' => 18,
                    'string_name' => 'Manage Labels',
                    'en' => 'Manage Labels',
                    'spa' => 'g├®rer les ├®tiquettes',
                    'por' => 'gerenciar r├│tulos',
                    'deu' => 'Labels verwalten',
                    'fra' => 'g├®rer les ├®tiquettes',
                ],
            17 =>
                [
                    'id' => 19,
                    'string_name' => 'Invoice Setting',
                    'en' => 'Invoice Setting',
                    'spa' => 'Ajuste de factura',
                    'por' => 'Configura├º├úo de fatura',
                    'deu' => 'Rechnungseinstellung',
                    'fra' => 'R├®glage de la facture',
                ],
            18 =>
                [
                    'id' => 20,
                    'string_name' => 'Task Reports',
                    'en' => 'Task Reports',
                    'spa' => 'Informes de tareas',
                    'por' => 'Relat├│rios de Tarefas',
                    'deu' => 'Aufgabenberichte',
                    'fra' => 'Rapports de t├óches',
                ],
            19 =>
                [
                    'id' => 21,
                    'string_name' => 'Task Setting',
                    'en' => 'Task Setting',
                    'spa' => 'Configuraci├│n de tareas',
                    'por' => 'Configura├º├úo de Tarefas',
                    'deu' => 'Aufgabenstellung',
                    'fra' => 'R├®glage de la t├óche',
                ],
            20 =>
                [
                    'id' => 22,
                    'string_name' => 'Weekly Usage',
                    'en' => 'Weekly Usage',
                    'spa' => 'Uso semanal',
                    'por' => 'Uso Semanal',
                    'deu' => 'W├Âchentliche Nutzung',
                    'fra' => 'Usage hebdomadaire',
                ],
            22 =>
                [
                    'id' => 24,
                    'string_name' => 'My Profile',
                    'en' => 'My Profile',
                    'spa' => 'Mi perfil',
                    'por' => 'Meu perfil',
                    'deu' => 'Mein Profil',
                    'fra' => 'Mon profil',
                ],
            23 =>
                [
                    'id' => 25,
                    'string_name' => 'Resource Utilization',
                    'en' => 'Resource Utilization',
                    'spa' => 'Utilizaci├│n de recursos',
                    'por' => 'Utiliza├º├úo de recursos',
                    'deu' => 'Ressourcennutzung',
                    'fra' => 'Utilisation des ressources',
                ],
            24 =>
                [
                    'id' => 26,
                    'string_name' => 'Change Password',
                    'en' => 'Change Password',
                    'spa' => 'Cambia la contrase├▒a',
                    'por' => 'Mudar senha',
                    'deu' => '├ändere das Passwort',
                    'fra' => 'Changer le mot de passe',
                ],
            25 =>
                [
                    'id' => 27,
                    'string_name' => 'Notifications',
                    'en' => 'Notifications',
                    'spa' => 'Notificaciones',
                    'por' => 'Notifica├º├Áes',
                    'deu' => 'Benachrichtigungen',
                    'fra' => 'Les notifications',
                ],
            26 =>
                [
                    'id' => 28,
                    'string_name' => 'Email Reports',
                    'en' => 'Email Reports',
                    'spa' => 'Informes de correo electr├│nico',
                    'por' => 'Relat├│rios por email',
                    'deu' => 'E-Mail-Berichte',
                    'fra' => 'Rapports de messagerie',
                ],
            27 =>
                [
                    'id' => 29,
                    'string_name' => 'Default View',
                    'en' => 'Default View',
                    'spa' => 'Vista predeterminada',
                    'por' => 'Visualiza├º├úo padr├úo',
                    'deu' => 'Standardansicht',
                    'fra' => 'Vue par d├®faut',
                ],
            28 =>
                [
                    'id' => 30,
                    'string_name' => 'Getting Started',
                    'en' => 'Getting Started',
                    'spa' => 'Empezando',
                    'por' => 'Come├ºando',
                    'deu' => 'Fertig machen',
                    'fra' => 'Commencer',
                ],
            29 =>
                [
                    'id' => 31,
                    'string_name' => 'Product Updates',
                    'en' => 'Product Updates',
                    'spa' => 'Actualizaciones de Producto',
                    'por' => 'Atualiza├º├Áes do produto',
                    'deu' => 'Produktaktualisierungen',
                    'fra' => 'Mises ├á jour du produit',
                ],
            31 =>
                [
                    'id' => 33,
                    'string_name' => 'Help Desk',
                    'en' => 'Help Desk',
                    'spa' => 'Mesa de ayuda',
                    'por' => 'Central de Ajuda',
                    'deu' => 'Beratungsstelle',
                    'fra' => 'bureau d\'aide',
                ],
            32 =>
                [
                    'id' => 34,
                    'string_name' => 'Chat Setting',
                    'en' => 'Chat Setting',
                    'spa' => 'Configuraci├│n de chat',
                    'por' => 'Configura├º├úo de bate-papo',
                    'deu' => 'Chat-Einstellung',
                    'fra' => 'Param├¿tres de chat',
                ],
            33 =>
                [
                    'id' => 35,
                    'string_name' => 'Pending Task',
                    'en' => 'Pending Task',
                    'spa' => 'Tarea pendiente',
                    'por' => 'Tarefa pendente',
                    'deu' => 'Ausstehende Aufgabe',
                    'fra' => 'T├óche en attente',
                ],
            35 =>
                [
                    'id' => 37,
                    'string_name' => 'Launchpad',
                    'en' => 'Launchpad',
                    'spa' => 'Rampe de lancement',
                    'por' => 'Plataforma de lan├ºamento',
                    'deu' => 'Launchpad',
                    'fra' => 'Rampe de lancement',
                ],
            36 =>
                [
                    'id' => 38,
                    'string_name' => 'Companies',
                    'en' => 'Companies',
                    'spa' => 'Compa├▒├¡as',
                    'por' => 'Empresas',
                    'deu' => 'Firmen',
                    'fra' => 'Entreprises',
                ],
            38 =>
                [
                    'id' => 40,
                    'string_name' => 'New',
                    'en' => 'New',
                    'spa' => 'Nuevo',
                    'por' => 'Novo',
                    'deu' => 'Neu',
                    'fra' => 'Nouveau',
                ],
            39 =>
                [
                    'id' => 41,
                    'string_name' => 'Analytics',
                    'en' => 'Analytics',
                    'spa' => 'Anal├¡tica',
                    'por' => 'Analytics',
                    'deu' => 'Analytics',
                    'fra' => 'Analytique',
                ],
            40 =>
                [
                    'id' => 42,
                    'string_name' => 'Others',
                    'en' => 'Others',
                    'spa' => 'otros',
                    'por' => 'outros',
                    'deu' => 'Andere',
                    'fra' => 'autres',
                ],
            41 =>
                [
                    'id' => 43,
                    'string_name' => 'Company Settings',
                    'en' => 'Company Settings',
                    'spa' => 'Ajustes de la empresa',
                    'por' => 'Configurações da empresa',
                    'deu' => 'Unternehmenseinstellungen',
                    'fra' => 'Paramètres de l\'entreprise',
                ],
            42 =>
                [
                    'id' => 44,
                    'string_name' => 'Personal Settings',
                    'en' => 'Personal Settings',
                    'spa' => 'Configuraciones personales',
                    'por' => 'Configurações pessoais',
                    'deu' => 'Persönliche Einstellungen',
                    'fra' => 'Paramètres personnels',
                ],
            43 =>
                [
                    'id' => 45,
                    'string_name' => 'Template',
                    'en' => 'Template',
                    'spa' => 'Modelo',
                    'por' => 'Modelo',
                    'deu' => 'Vorlage',
                    'fra' => 'Modèle',
                ],
            44 =>
                [
                    'id' => 46,
                    'string_name' => 'Status Workflow Setting',
                    'en' => 'Status Workflow Setting',
                    'spa' => 'Configuración de flujo de trabajo de estado',
                    'por' => 'Configuração do fluxo de trabalho de status',
                    'deu' => 'Status Workflow Einstellung',
                    'fra' => 'Statut du flux de travail',
                ],
        ];
        $this->table('menu_languages')->insert($data)->save();
    }
}
