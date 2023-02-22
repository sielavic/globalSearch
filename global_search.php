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

        $query_task =("SELECT id,full,iniciator FROM multitask WHERE id LIKE '%".$q."%' OR full LIKE '%".$q."%'");
        $query_agree =("SELECT id,full FROM multitask_agreement WHERE id LIKE '%".$q."%' OR full LIKE '%".$q."%'");
        
        $queryResultTask = $CI->db->query($query_task);
        $result_task = $queryResultTask->result();

        $queryResultAgree = $CI->db->query($query_agree);
        $result_agree = $queryResultAgree->result();
        
        if (!empty($result_agree)){
            $result = array_merge($result_task, $result_agree);
        }else{
            $result = $result_task;
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
