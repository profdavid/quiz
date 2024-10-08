<!-- [ Main Content ] start -->
<!-- [ Main Content ] start -->
<div class="pcoded-main-container">
    <div class="pcoded-wrapper">
        <div class="pcoded-content">
            <div class="pcoded-inner-content">
                <!-- [ breadcrumb ] start -->
                <div class="page-header">
                    <div class="page-block">
                        <div class="row align-items-center">
                            <div class="col-md-12">
                                <div class="page-header-title">
                                    <h5 class="m-b-10">Home</h5>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <!-- [ breadcrumb ] end -->
                
                <div class="main-body">
                    <div class="page-wrapper">
                        <!-- [ Main Content ] start -->
                        <div class="row">
                            <div class="col-sm-12">
                                {RES_ERRO}

                                <div class="card">
                                    <div class="card-header d-flex justify-content-between align-items-center">
                                        <h3 class="text-success m-0">Quiz Ifes</h3>
                                        <img width="150px" src="<?=base_url('assets/img/logo_cor.png')?>" alt="logo_ifes">
                                    </div>
                                    <div class="card-body">
                                        <span class="f-16">Jogo - Motivação - Interação - Integração</span>
                                    </div>
                                    <div class="card-footer">
                                        <span class="text-danger">Desenvolvido por: Prof. David Paolini Develly e Aluno Luis Gustavo Leal Rossim - Campus Colatina</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <!-- [ Main Content ] end -->
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<!-- [ Main Content ] end -->

<script>
window.onload = function(){
    {RES_OK}

    $('#tabListagem').DataTable({
        "language": {
            "url": "<?php echo base_url('assets/plugins/data-tables/json/dataTables.ptbr.json') ?>"
        },
        "aaSorting": []        
    });
};
</script>
