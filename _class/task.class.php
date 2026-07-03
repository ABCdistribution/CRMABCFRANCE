<?php 

class task {

    public static $prioriteMap = [
        0 => "Basse",
        1 => "Normale",
        2 => "Haute"
    ];

    public static function creation( $task ) {
        $appid = $task['appid'];
        if( self::get($appid) ) return; // La tache existe déjà

        # User
        $id_user = ( defined('API_ID_USER') && API_ID_USER > 0 ? API_ID_USER : 0);
        if( !$id_user ) return core::logApk("[task-creation] Utilisateur introuvable");
        $user = user::exist($id_user);
        $id_repr = $user['id_repr'];

        # Prospect
        global $db;
        $id_target = "";
        if( $task['target'] == "prospect" )
            $id_target = prospect::getIdFromAppId( $task['id_target'] );
        if( $task['target'] == "client" ) {
            $id_target = intval($task['id_target']);     
            $db->execute("SELECT id_as400 FROM ref_client WHERE id = ".$id_target);
            $id_target = $db->assoc()['id_as400'];
        }
            
        # Priorité
        $priorite = 0;
        if( $task['priorite'] == "Normale" ) $priorite = 1;
        if( $task['priorite'] == "Haute" ) $priorite = 2;
        
        $db->execute("
            INSERT INTO task
            (appid,id_repr,target,id_target,type,action,libelle,date_task,priorite)
            VALUES
            (
                '".$db->escape($task['appid'])."',
                '".$id_repr."',
                '".$db->escape($task['target'])."',
                '".$id_target."',
                '".$db->escape($task['taskType'])."',
                '".$db->escape($task['actionType'])."',
                '".$db->escape($task['libelle'])."',
                '".$db->escape($task['date'])."',
                '".$priorite."'
            )        
        ");

        return;
    }
    public static function get( $appid ) {
        global $db;
        $db->execute("
            SELECT * FROM 
                task 
            WHERE 
                ( 
                    id = '".( intval($appid) == $appid ? intval($appid) : -1 )."' 
                    OR appid = '".$db->escape($appid)."' 
                ) 
                AND deleted = 0
        ");
        if( !$db->num() ) return false;
        return $db->assoc();        
    }
    public static function getTasks() {
        $id_user = ( defined('API_ID_USER') && API_ID_USER > 0 ? API_ID_USER : 0);
        if( !$id_user ) return core::logApk("[task-getTasks] Utilisateur introuvable");
        $user = user::exist($id_user);
        $id_repr = $user['id_repr'];
        global $db;
        $from = date('Y-m-d',strtotime('-3 months'));
        $to = date('Y-m-d',strtotime('+1 year'));
        $db->execute("
            SELECT * FROM task
            WHERE
                deleted = 0
                AND id_repr = '$id_repr'
                AND date_task BETWEEN '$from' AND '$to'
            ORDER BY
                priorite DESC
        ");
        if( !$db->num() ) api::ajaxRep([]);

        $datas = $db->getArray();
        $tasks = [];
        foreach( $datas as $k=>$e ) {
            $key = date('Y-m-d',strtotime($e['date_task']));
            if( !isset($tasks[$key]) ) $tasks[$key] = [];
            $id_target = $e['id_target'];
            if( $e['target'] == "prospect" ) {
                $db->execute("SELECT appid FROM prospect WHERE id = '$id_target' AND deleted = 0");
                $id_target = $db->num() ? $db->assoc()['appid'] : 0;
            }

            $tasks[$key][] = [
                "appid" => $e['appid'],
                "id_target" =>  $id_target,
                "target" =>  $e['target'],
                "taskType" =>  $e['type'],
                "actionType" =>  $e['action'],
                "libelle" =>  $e['libelle'],
                "date" =>  $e['date_task'],
                "priorite" =>  self::$prioriteMap[$e['priorite']],
                "statut" => $e['statut'],
                "date_validation" => core::dateOutput($e['date_validation'],true),
            ];
        }
        api::ajaxRep($tasks);
    }

    public static function validate( $params ) {
        $task = self::get($params['appid']);
        if( !$task ) return;
        $date = $params['validation'];
        if( strpos($date,'0000') < - 1 ) $date = date('Y-m-d G:i:s');
        global $db;
        $db->execute("
            UPDATE task SET 
                statut = 1, 
                date_validation = '".$db->escape($date)."' 
            WHERE 
                id = ".$task['id']
        );
        return;
    }

    public static function edit( $task ) {
        $appid = $task['appid'];
        if( !self::get($appid) ) return; // La tache n'existe pas
        # Priorité
        $priorite = 0;
        if( $task['priorite'] == "Normale" ) $priorite = 1;
        if( $task['priorite'] == "Haute" ) $priorite = 2;        
        global $db;
        $db->execute("
            UPDATE task
            SET
                type = '".$db->escape($task['taskType'])."',
                action = '".$db->escape($task['actionType'])."',
                libelle = '".$db->escape($task['libelle'])."',
                date_task = '".$db->escape($task['date'])."',
                priorite = '".$priorite."'
            WHERE
                appid = '".$task['appid']."'
                AND deleted = 0
        ");
        return;
    }

    public static function delete( $params ) {
        $task = self::get($params['appid']);
        if( !$task ) return;
        global $db;
        $db->execute("
            UPDATE task SET 
                deleted = 1
            WHERE 
                id = ".$task['id']
        );
        return;
    }    

    public static function getPlanning() {
        $id_user = intval($_POST['id_user']);
        $from = e(trim($_POST['from']));
        $to = isset($_POST['to']) && $_POST['to'] != "" ? trim($_POST['to']) : false;
        $isClient = $_POST['isClient'] == "true";

        $user = user::exist($id_user);
        if( !$user ) core::aError("Utilisateur introuvable");

        $w = [];
        $w[] = "deleted = 0";
        $w[] = "id_repr = '".$user['id_repr']."'";
        $w[] = "date_task >= '".$from."' ";
        if( $to )
            $w[] = "date_task <= '".e($to)."' ";

        $w[] = "target = '".($isClient ? 'client' : 'prospect')."' ";

        global $db;
        $db->execute("
            SELECT * FROM
                task
            WHERE
                ".implode(' AND ',$w)."
            ORDER BY date_task DESC,statut ASC ,priorite DESC
        ");

        if( !$db->num() ) die(json_encode(["tasks"=>[]]));
        $tasks = [];
        while( $r = $db->assoc() ) $tasks[] = $r;

        $prospects = prospect::getDeportedProspects();

        foreach( $tasks as $k=>$e ) {
            $tasks[$k]['date_task'] = core::dateOutput($e['date_task']);
            $tasks[$k]['date_validation'] = core::dateOutput($e['date_validation'], true);
            $tasks[$k]['priorite'] = self::$prioriteMap[$e['priorite']];
            if( $e['target'] == "prospect" ) {
                $tasks[$k]['prospect'] = [];
                foreach( $prospects as $i=>$j )
                    if( $j['id'] == $e['id_target'] )
                        $tasks[$k]['prospect'] = $j;
            }
            else {
                $c = client::getByCode($e['id_target']);
                if( !$c ) {
                    unset($tasks[$k]);
                    continue;
                }
                $tasks[$k]['client'] = $c;
            }
                
        }
        die(json_encode(["tasks" => $tasks]));
    }

    public static function printTasks( $id, $type  ) {
        global $db;
        $db->execute("
            SELECT 
                *
            FROM
                task t
            WHERE
                target = '$type'
                AND id_target = $id
                AND deleted = 0
            ORDER BY date_task DESC
        ");
        if( !$db->num() ) return l('no-results');
        $datas = $db->getArray();
        $content = [];
        $content[] = '
        <table class="table table-condensed table-hover">
        <thead>
            <tr>
                <th>'.l('client-task-date-prevue').'</th>
                <th>'.l('client-task-date-resiliation').'</th>
                <th>'.l('client-task-repr').'</th>
                <th>'.l('client-task-type').'</th>
                <th>'.l('client-task-action').'</th>
                <th>'.l('client-task-description').'</th>
                <th>'.l('client-task-priorite').'</th>
                <th>'.l('client-task-statut').'</th>
            </tr>
        </thead>
        <tbody>
        ';
        foreach( $datas as $e ) {
            $u = $e['id_repr'] ? user::getFromIdRepr($e['id_repr']) : ["displayname" => ""];

            $content[] = '
                <tr class="'.($e['statut'] ? 'bg-success text-white' : '').'">
                    <td>'.core::dateOutput($e['date_task']).'</td>
                    <td>'.($e['statut'] == 1 ? core::dateOutput($e['date_validation'],true) : '' ).'</td>
                    <td>'.$u['displayname'].'</td>
                    <td>'.$e['type'].'</td>
                    <td>'.$e['action'].'</td>
                    <td>'.$e['libelle'].'</td>
                    <td>'.self::$prioriteMap[$e['priorite']].'</td>
                    <td>'.l($e['statut'] ? 'task-fait' : 'task-a-faire').'</td>
                </tr>            
            ';
        }
        $content[] = '</tbody></table>';
        return implode($content);
    }

}