<div id="addNewComponents">
    <div class="componentsWrapper">
        <waboot-component v-for="component in available_components" :data="component" :key="component.slug" inline-template>
            <div class="component-card" :data-component="data.slug">
                <div class="component-card-top">
                    <div class="name column-name">
                        <h3>{{ data.title }}</h3>
                    </div>
                    <div class="action-links">
                        <ul class="component-action-buttons">
                            <li><a href="#" class="button download-now">Download</a></li>
                        </ul>
                    </div>
                    <div class="desc column-description">
                        <p>
                            {{ data.description }}
                        </p>
                        <p class="authors">
                            <cite>By </cite>{{ data.author }}
                        </p>
                    </div>
                </div>
                <div class="component-card-bottom">

                </div>
            </div>
        </waboot-component>
    </div>
</div>