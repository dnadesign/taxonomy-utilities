<!doctype html>
<html class="no-js" lang="$ContentLocale">
    <head>
        <% base_tag %>
        <title>Taxonomy Report | $SiteConfig.Title.XML</title>
        $MetaTags(false)
        <meta name="viewport" id="viewport" content="width=device-width,minimum-scale=1.0,maximum-scale=10.0,initial-scale=1.0" />
        <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/css/bootstrap.min.css" integrity="sha384-BVYiiSIFeK1dGmJRAkycuHAHRg32OmUcww7on3RYdg4Va+PmSTsz/K68vbdEjh4u" crossorigin="anonymous">
        <% require css("vendor/dnadesign/taxonomy-utilities/client/dist/css/style.min.css") %>
    </head>
    <body style="font-size:initial;">
    <!-- ============================================================== -->
    <!-- Preloader - style you can find in spinners.css -->
    <!-- ============================================================== -->
    <div class="preloader">
        <div class="lds-ripple">
            <div class="lds-pos"></div>
            <div class="lds-pos"></div>
        </div>
    </div>
    <!-- ============================================================== -->
    <!-- Main wrapper - style you can find in pages.scss -->
    <!-- ============================================================== -->
    <div id="main-wrapper" data-navbarbg="skin6" data-theme="light" data-layout="vertical" data-sidebartype="full" data-boxed-layout="full">
        <aside class="left-sidebar" data-sidebarbg="skin5">
            <!-- Sidebar scroll-->
            <div class="scroll-sidebar">
                <!-- Sidebar navigation-->
                <nav class="sidebar-nav">
                    <ul id="sidebarnav">
                        <li class="sidebar-item">
                            <a class="sidebar-link waves-effect waves-dark sidebar-link" href="/taxonomysearchreport/list/$ReferrerID" aria-expanded="false">
                                <i class="mdi mdi-chevron-left"></i>
                                <span class="hide-menu">Back</span>
                            </a>
                        </li>
                    </ul>
                </nav>
                <!-- End Sidebar navigation -->
            </div>
            <!-- End Sidebar scroll-->
        </aside>
        <!-- ============================================================== -->
        <!-- End Left Sidebar - style you can find in sidebar.scss  -->
        <!-- ============================================================== -->
        <!-- ============================================================== -->
        <!-- Page wrapper  -->
        <!-- ============================================================== -->
        <div class="page-wrapper">
            <!-- ============================================================== -->
            <!-- Bread crumb and right sidebar toggle -->
            <!-- ============================================================== -->
            <div class="page-breadcrumb">
                    <div class="row">
                        <div class="col-5 align-self-center">
                            <h4 class="page-title">$ListOfResults.Matches.TotalItems results
                            </h4>
                        </div>
                    </div>
                </div>
            <!-- ============================================================== -->
            <!-- End Bread crumb and right sidebar toggle -->
            <!-- ============================================================== -->
            <!-- ============================================================== -->
            <!-- Container fluid  -->
            <!-- ============================================================== -->
            <div class="container-fluid">                
                <!-- ============================================================== -->
                <!-- Ravenue - page-view-bounce rate -->
                <!-- ============================================================== -->
                <div class="row">
                    <!-- column -->
                    <div class="col-12">
                        <div class="card">
                            <div class="card-header">
                                <h4 class="">Search</h4>
                            </div>
                            <div class="card-body">
                                $TaxonomySearchForm
                            </div>
                        </div>
                    </div>
                </div>                
                <div class="row">
                    <!-- column -->
                    <div class="col-12">
                        <div class="card">
                            <div class="card-body">
                                <h4 class="card-title"></h4>
                            </div>
                            <div class="table-responsive">
                                <table class="table table-hover">
                                    <thead>
                                        <tr>
                                            <th class="border-top-0"></th>
                                            <th class="border-top-0">TYPE</th>
                                            <th class="border-top-0">TITLE/NAME</th>
                                            <th class="border-top-0">TAGS</th>
                                            <th class="border-top-0"></th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                    <% with $ListOfResults %>
                                        <% loop $Matches %>
                                        <tr>                                        
                                            <td class="txt-oflo">$Top.getEntryNumber($PageStart, $Pos)</td>
                                            <td class="txt-oflo">$singular_name</td>
                                            <td class="txt-oflo"><% if $Title %>$Title<% else %>$Name<% end_if %></td>                           
                                            <td class="txt-oflo">
                                                <% loop $Tags.Sort('Name ASC') %>
                                                    <span class="label label-primary">$Name</span>
                                                <% end_loop %>
                                            </td>
                                            <% if $CMSEditLink %>
                                                <td class="txt-oflo"><a href="$CMSEditLink" target="_blank">Edit</td>
                                            <% end_if %>                                        
                                        </tr>
                                        <% end_loop %>
                                    <% end_with %>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>

                    <div class="col-12">
                        <div class="text-xs-center">
                            <% with $ListOfResults.Matches %>
                                <% if $MoreThanOnePage %>
                                <ul class="pagination justify-content-center">
                                    <% if $NotFirstPage %>
                                        <li class="page-item"><a class="page-link" href=" $PrevLink">Prev</a>
                                    <% end_if %>
                                    <% loop $PaginationSummary(4) %>
                                        <% if CurrentBool %>
                                            <li class="page-item active"><span class="page-link">$PageNum</span></li>
                                        <% else %>
                                            <% if Link %>
                                                <li class="page-item"><a href="$Link" class="page-link">$PageNum</a></li>
                                            <% else %>
                                                <li class="page-item">...</li>
                                            <% end_if %>
                                        <% end_if %>
                                    <% end_loop %>
                                    <% if $NotLastPage %>
                                        <li class="page-item"><a class="next" href=" $NextLink" class="page-link">Next</a></li>
                                    <% end_if %>
                                </ul>
                                <% end_if %>
                            <% end_with %>
                        </div>
                    </div>
                </div>               
                    
            </div>
            <!-- ============================================================== -->
            <!-- End Container fluid  -->
            <!-- ============================================================== -->
            <!-- ============================================================== -->
            <!-- footer -->
            <!-- ============================================================== -->
            <footer class="footer text-center">
                
            </footer>
            <!-- ============================================================== -->
            <!-- End footer -->
            <!-- ============================================================== -->
        </div>
        <!-- ============================================================== -->
        <!-- End Page wrapper  -->
        <!-- ============================================================== -->
    </div>
    <!-- ============================================================== -->
    <!-- End Wrapper -->
    <!-- ============================================================== -->
    <% require javascript(vendor/dnadesign/taxonomy-utilities/client/dist/assets/libs/jquery/dist/jquery.min.js) %>
    <!-- Bootstrap tether Core JavaScript -->
    <% require javascript(vendor/dnadesign/taxonomy-utilities/client/dist/assets/libs/popper.js/dist/umd/popper.min.js) %>
    <% require javascript(vendor/dnadesign/taxonomy-utilities/client/dist/assets/libs/bootstrap/dist/js/bootstrap.min.js) %>
    <!-- slimscrollbar scrollbar JavaScript -->
    <% require javascript(vendor/dnadesign/taxonomy-utilities/client/dist/assets/extra-libs/sparkline/sparkline.js) %>
    <!--Wave Effects -->
     <% require javascript(vendor/dnadesign/taxonomy-utilities/client/dist/js/waves.js) %>
    <!--Menu sidebar -->
     <% require javascript(vendor/dnadesign/taxonomy-utilities/client/dist/js/sidebarmenu.js) %>
    <!--Custom JavaScript -->
     <% require javascript(vendor/dnadesign/taxonomy-utilities/client/dist/js/custom.min.js) %>
    <!--This page JavaScript -->
    <!--chartis chart-->
    <% require javascript(vendor/dnadesign/taxonomy-utilities/client/dist/assets/libs/chartist/dist/chartist.min.js) %>
    <% require javascript(vendor/dnadesign/taxonomy-utilities/client/dist/assets/libs/chartist-plugin-tooltips/dist/chartist-plugin-tooltip.min.js) %>

    </body>
</html>