<?php global $page_title; $page_title = __('Add Components','waboot'); ?>

<?php require_once get_template_directory() . '/wbf/admin-header.php'; ?>

<div id="addNewComponents">
    <div class="componentsWrapper">
        <waboot-component v-for="component in available_components" :data="component" :key="component.slug" inline-template>
            <div class="component-card" :data-component="data.slug">
                <div class="component-card-top">
                    <div class="thumbnail column-thumbnail">
                        <img :src="data.thumbnail" class="component-icon" />
                    </div>
                    <div class="desc column-description">
                        <h3>{{ data.title }}</h3>
                        <p>
                            {{ data.description }}
                        </p>
                        <p class="authors">
                            <cite>By </cite>{{ data.author }}
                        </p>
                    </div>
                    <div class="action-links">
                        <ul class="component-action-buttons">
                            <li><a href="#" class="button download-now">Download</a></li>
                        </ul>
                    </div>
                </div>
                <div class="component-card-bottom">

                </div>
            </div>
        </waboot-component>
    </div>
</div>