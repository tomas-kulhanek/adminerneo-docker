<?php

namespace docker {
    function adminer_object() {
        require_once('plugins/plugin.php');

        class Adminer extends \AdminerPlugin {
            function _callParent($function, $args) {
                if ($function === 'loginForm') {
                    ob_start();
                    $return = \Adminer::loginForm();
                    $form = ob_get_clean();

                    if (getenv('ADMINER_DEFAULT_DRIVER') != 'server') {
                        $form = str_replace(
                            'option value="server" selected',
                            'option value="server"',
                            $form
                        );
    
                        $form = str_replace(
                            'option value="'.(getenv('ADMINER_DEFAULT_DRIVER')).'"',
                            'option value="'.(getenv('ADMINER_DEFAULT_DRIVER')).'" selected',
                            $form
                        );
                    }

                    $form = str_replace(
                        'name="auth[server]" value="" title="hostname[:port]"',
                        'name="auth[server]" value="'.(getenv('ADMINER_DEFAULT_SERVER') ?: 'db').'" title="hostname[:port]"',
                        $form
                    );

                    $form = str_replace(
                        'name="auth[username]" id="username" value=""',
                        'name="auth[username]" value="'.(getenv('ADMINER_DEFAULT_USER') ?: '').'"',
                        $form
                    );

                    $form = str_replace(
                        'name="auth[password]"',
                        'name="auth[password]" value="'.(getenv('ADMINER_DEFAULT_PASSWORD') ?: '').'"',
                        $form
                    );

                    $form = str_replace(
                        'name="auth[db]"',
                        'name="auth[db]" value="'.(getenv('ADMINER_DEFAULT_DB') ?: '').'"',
                        $form
                    );

                    echo $form;
                    return $return;
                }

                return parent::_callParent($function, $args);
            }
        }

        $plugins = [];
        foreach (glob('plugins-enabled/*.php') as $plugin) {
            $plugins[] = require($plugin);
        }

        if (is_dir('plugins-custom')) {
            foreach (glob('plugins-custom/*.php') as $plugin) {
                $plugins[] = require($plugin);
            }
        }

        return new Adminer($plugins);
    }
}

namespace {
    if (basename($_SERVER['DOCUMENT_URI'] ?? $_SERVER['REQUEST_URI']) === 'adminer.css' && is_readable('adminer.css')) {
        header('Content-Type: text/css');
        readfile('adminer.css');
        exit;
    }

    function adminer_object() {
        return \docker\adminer_object();
    }

    require('adminer.php');
}
