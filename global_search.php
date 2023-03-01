public function getTasksForGlobal()
    {
        $q = $this->input->get('q');
        $page = $this->input->get('page');
        $templateName = $this->input->get('template');
        $pageLimit = $this->input->get('pageLimit');
        $workType = $this->input->get('workType');
        $ids = $this->input->get('ids');
        $only_active = $this->input->get('only_active');


        $page--; // чтобы не пропускал 1 страницу, т.к. счёт переменной начинается от 1

        $CI =& get_instance();
        $CI->load->database();


        $result_task = [];


        if ($this->currentUser->hasPermission(8)) {
            $query_task_without =("SELECT id,full,iniciator,kurator  FROM multitask
                      WHERE multitask.id LIKE '%".$q."%' OR multitask.full LIKE '%".$q."%'");
            $queryResultTask = $CI->db->query($query_task_without);
            $result_task = $queryResultTask->result();
        } else {
            $query_task =("SELECT multitask.id,multitask.full,multitask.iniciator,multitask.kurator, work_user.wus_user_id  FROM multitask
                       LEFT JOIN work ON multitask.id = work.wrk_macrotaskid AND work.wrk_type = 'task'
                       LEFT JOIN work_user ON work.wrk_id = work_user.wus_wrk_id	
                       WHERE multitask.id LIKE '%".$q."%' OR multitask.full LIKE '%".$q."%'");

            $resultTaskResp = $CI->db->query($query_task);
            $qwery_tasks = $resultTaskResp->result();
            foreach ($qwery_tasks as $qwery_task) {
                if ($qwery_task->iniciator == USER_COOKIE_ID) {
                    $result_task[] = $qwery_task;
                }
                if ($qwery_task->kurator == USER_COOKIE_ID) {
                    $result_task[] = $qwery_task;
                }
                if ($qwery_task->wus_user_id == USER_COOKIE_ID) {
                    $result_task[] = $qwery_task;
                }
            }
        }

        $result_agree =[];

        if ($this->currentUser->hasPermission(24)) {
            $query_agree =("SELECT id,full FROM multitask_agreement WHERE id LIKE '%".$q."%' OR full LIKE '%".$q."%'");
            $queryResultAgree = $CI->db->query($query_agree);
            $result_agree = $queryResultAgree->result();
        }else{
            $query_agre =("SELECT multitask_agreement.id, multitask_agreement.full, multitask_agreement.iniciator_id, multitask_agreement.kurator, work_user.wus_user_id  FROM multitask_agreement
                       LEFT JOIN work ON multitask_agreement.id = work.wrk_macrotaskid AND work.wrk_type = 'agre'
                       LEFT JOIN work_user ON work.wrk_id = work_user.wus_wrk_id	
                       WHERE multitask_agreement.id LIKE '%".$q."%' OR multitask_agreement.full LIKE '%".$q."%'");
            $queryResultAgree = $CI->db->query($query_agre);
            $qwery_agree = $queryResultAgree->result();
            foreach ($qwery_agree as $qwery_agre) {
                if ($qwery_agre->iniciator_id == USER_COOKIE_ID) {
                    $result_agree[] = $qwery_agre;
                }
                if ($qwery_agre->kurator == USER_COOKIE_ID) {
                    $result_agree[] = $qwery_agre;
                }
                if ($qwery_agre->wus_user_id == USER_COOKIE_ID) {
                    $result_agree[] = $qwery_agre;
                }
            }

        }





        if (!empty($result_agree) && !empty($result_task)){
            $result = array_merge($result_task, $result_agree);
        }else if (!empty($result_task)){
            $result = $result_task;
        } else{
            $result = $result_agree;
        }


        $totalCount = count($result);

        if (!empty($result)){
            foreach ($result as &$task){
                // получим верстку варианта выбора
                ob_start();
                include "./application/views/common/components/" . $templateName. ".php";
                $task->htmlContent = ob_get_contents();
                ob_clean();
                // теперь получим верстку выбранного значения, которая подставится при выборе элемента
                ob_start();
                include "./application/views/common/components/" . $templateName. "_s.php";
                $task->htmlContentSelected = ob_get_contents();
                ob_clean();
            }
        }

        echo json_encode(array('items' => $result, 'total_count' => $totalCount));
    }
