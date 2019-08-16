<div id="main_about">
    <div class="gpage-company_patt--top">
        <img src="<?=$app['base_url']?>/images/about/patt_land.png" alt="">
    </div>

    <div class="gcom-banner">
        <img src="<?=$app['base_url']?>/images/about/heniart_web-about.jpg" alt="" class="img-responsive">
    </div>
    <div id="main" class="">

        <div class="gpage-title_gap"></div>

        <div class="container">

            <div class="gpage-title_wrap">
                <div class="row">
                    <div class="col-md-6">

                        <div class="gpage-title text-uppercase">
                            <span class="line-1"><?= unik('regions:region_field', 'about', 'about_us', 'value'.$lang_site); ?></span>
                        </div>

                    </div>

                    <div class="col-md-6">
                        <div class="gpage-title_line">
                            <div class="gpage-title_desc">
                                <p>
                                    <?= unik('regions:region_field', 'about', 'description_about_us', 'value'.$lang_site); ?>
                                </p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="clearfix"></div>

            <div class="gpage-content">

                <div class=" hidden-xs hidden-sm" style="height: 50px"></div>

                <div class="row">
                    <div class="col-md-6">
                        <h3 class="gp-typo_h3"><?= unik('regions:region_field', 'about', 'Vision', 'value'.$lang_site); ?></h3>
                        <div class="gp-typo_h5 fw300">
                            <p>
                                <?= unik('regions:region_field', 'about', 'Desc_Vision', 'value'.$lang_site); ?>
                            </p>
                        </div>
                    </div>

                    <div class="col-md-6">
                        <h3 class="gp-typo_h3"><?= unik('regions:region_field', 'about', 'Misson', 'value'.$lang_site); ?></h3>
                        <div class="gp-typo_h5 fw300">
                            <p>
                                <?= unik('regions:region_field', 'about', 'Desc_Mission', 'value'.$lang_site); ?>
                            </p>
                        </div>
                    </div>

                </div>

                <div class=" hidden-xs hidden-sm" style="height: 70px"></div>

                <div class="clearfix">
                    <div class="gcore">

                        <h2 class="gp-typo_h2 visible-md visible-lg pt60 text-uppercase"><?= unik('regions:region_field', 'about', 'CoreValue', 'value'.$lang_site); ?></h2>
                        <h2 class="gp-typo_h4 hidden-md hidden-lg text-uppercase"><?= unik('regions:region_field', 'about', 'CoreValue', 'value'.$lang_site); ?></h2>

                        <img src="<?=$app['base_url']?>/images/about/line-dashed.png" alt="">
                        <div class="mt30 hidden-md hidden-lg"></div>

                        <div class="gp-contbox">
                            <p>
                                <?= unik('regions:region_field', 'about', 'Desc_CoreValue', 'value'.$lang_site); ?>
                            </p>
                        </div>

                        <div class="mt70 visible-md visible-lg"></div>
                        <div class="mt30 hidden-md hidden-lg"></div>

                        <div class="clearfix">
                            <img src="<?=$app['base_url']?>/images/about/core_value.jpg" alt="" class="img-responsive">
                        </div>

                        <div class="mt60 visible-md visible-lg"></div>
                        <div class="mt30 hidden-md hidden-lg"></div>
                        <div class="clearfix row ">
                            <div class="col-md-2">
                                <h4 class="gp-typo_h4"><?= unik('regions:region_field', 'about', 'Culture', 'value'.$lang_site); ?></h4>
                            </div>
                            <div class="col-md-10">
                                <div class="gp-typo_body">
                                    <p>
                                        <?= unik('regions:region_field', 'about', 'Desc_Culture', 'value'.$lang_site); ?>
                                    </p>
                                </div>
                            </div>
                        </div>

                    </div>
                </div>

            </div>

        </div>


        <div class="gpeople-wrapper">
            
            <div id="ceoSlider_1">
               <?php foreach ($humans as $item) { ?>
               <div class="gpeople-item" style="width: 100%; display: inline-block;">

                  <img class="gpeople-item_thumb" src="<?= unik()->pathToUrl($item['Img']);?>" alt="Nguyen Hai Trieu" style="width: 100%;">

                  <div class="gpeople-item_info">
                      <div class="gpeople-item_info_title gp-typo_body text-uppercase fw500"><?= $item['Department'.$lang_site]?></div>
                      <div class="gpeople-item_info_name gp-typo_h4 text-capitalize fw600"><?= $item['Name'.$lang_site]?></div>
                      <div class="gpeople-item_info_desc gp-typo_body "><?= $item['Description'.$lang_site]?></div>
                  </div>
               </div>
               <?php } ?>
            </div>
        </div>

        <div class="container">
            <h4 class="gp-typo_h4">We provide you <img src="<?=$app['base_url']?>/images/about/line-dashed.png" alt=""></h4>

            <div class="mt45 visible-md visible-lg"></div>
            <div class="mt15 hidden-md hidden-lg"></div>
            <div class="gsquares">
               <?php foreach ($services as $item) { ?>
                  
                  <div class="gsquares-item">

                    <a href="<?= $app['base_url'] ?>/services/<?= $item['name_slug'] ?>">
                        <img class="gsquares-item_thumb" src="<?= unik()->pathToUrl($item['img']);?>" alt="">

                        <img class="gsquares-item_icon visible-md visible-lg" src="<?= unik()->pathToUrl($item['icon']);?>" alt="">
                        <h5 class="gp-typo_h5 gsquares-item_head visible-md visible-lg"><?= $app('util')->getLang('Golden',$language) ?> <?= $item['name'.$lang_site]?></h5>

                        <div class="gsquares-item_info hidden-md hidden-lg">
                            <img class="img-responsive" src="<?= unik()->pathToUrl($item['icon']);?>" alt="">
                            <h5 class="gp-typo_h5"><?= $app('util')->getLang('Golden',$language) ?> <?= $item['name'.$lang_site]?> </h5>
                        </div>
                    </a>

                </div>
               <?php } ?>
            </div>

        </div>

    </div>
</div>