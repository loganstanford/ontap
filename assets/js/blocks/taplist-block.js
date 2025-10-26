/**
 * OnTap Taplist Gutenberg Block
 *
 * @package OnTap
 * @since   1.0.0
 */

(function() {
    const { registerBlockType } = wp.blocks;
    const { InspectorControls, useBlockProps } = wp.blockEditor;
    const { PanelBody, SelectControl, RangeControl, ToggleControl } = wp.components;
    const { __ } = wp.i18n;
    const { ServerSideRender } = wp.serverSideRender ? wp.serverSideRender : wp.components.ServerSideRender;

    registerBlockType('ontap/taplist', {
        title: __('OnTap Taplist', 'ontap'),
        description: __('Display a list of beers currently on tap', 'ontap'),
        icon: 'beer',
        category: 'widgets',
        attributes: {
            taproom: {
                type: 'string',
                default: ''
            },
            layout: {
                type: 'string',
                default: 'grid'
            },
            columns: {
                type: 'number',
                default: 3
            },
            showFilters: {
                type: 'boolean',
                default: true
            },
            showSearch: {
                type: 'boolean',
                default: true
            },
            showSort: {
                type: 'boolean',
                default: true
            },
            showImage: {
                type: 'boolean',
                default: true
            },
            showTapNumber: {
                type: 'boolean',
                default: true
            },
            showStyle: {
                type: 'boolean',
                default: true
            },
            showAbv: {
                type: 'boolean',
                default: true
            },
            showIbu: {
                type: 'boolean',
                default: true
            },
            showDescription: {
                type: 'boolean',
                default: true
            },
            showContainers: {
                type: 'boolean',
                default: true
            },
            postsPerPage: {
                type: 'number',
                default: 12
            },
            pagination: {
                type: 'boolean',
                default: true
            },
            orderBy: {
                type: 'string',
                default: 'tap_number'
            },
            order: {
                type: 'string',
                default: 'ASC'
            }
        },

        edit: function(props) {
            const { attributes, setAttributes } = props;
            const blockProps = useBlockProps();

            // Get taproom options (you might want to fetch these via API)
            const taproomOptions = [
                { label: __('All Taprooms', 'ontap'), value: '' },
                // Additional taprooms would be loaded here
            ];

            return (
                <div {...blockProps}>
                    <InspectorControls>
                        {/* Layout Settings */}
                        <PanelBody title={__('Layout Settings', 'ontap')} initialOpen={true}>
                            <SelectControl
                                label={__('Taproom', 'ontap')}
                                value={attributes.taproom}
                                options={taproomOptions}
                                onChange={(value) => setAttributes({ taproom: value })}
                            />

                            <SelectControl
                                label={__('Layout', 'ontap')}
                                value={attributes.layout}
                                options={[
                                    { label: __('Grid', 'ontap'), value: 'grid' },
                                    { label: __('List', 'ontap'), value: 'list' },
                                    { label: __('Table', 'ontap'), value: 'table' }
                                ]}
                                onChange={(value) => setAttributes({ layout: value })}
                            />

                            {attributes.layout === 'grid' && (
                                <RangeControl
                                    label={__('Columns', 'ontap')}
                                    value={attributes.columns}
                                    onChange={(value) => setAttributes({ columns: value })}
                                    min={1}
                                    max={6}
                                />
                            )}

                            <RangeControl
                                label={__('Beers Per Page', 'ontap')}
                                value={attributes.postsPerPage}
                                onChange={(value) => setAttributes({ postsPerPage: value })}
                                min={1}
                                max={100}
                            />

                            <ToggleControl
                                label={__('Show Pagination', 'ontap')}
                                checked={attributes.pagination}
                                onChange={(value) => setAttributes({ pagination: value })}
                            />

                            <SelectControl
                                label={__('Order By', 'ontap')}
                                value={attributes.orderBy}
                                options={[
                                    { label: __('Tap Number', 'ontap'), value: 'tap_number' },
                                    { label: __('Name', 'ontap'), value: 'name' },
                                    { label: __('Date Added', 'ontap'), value: 'date_added' }
                                ]}
                                onChange={(value) => setAttributes({ orderBy: value })}
                            />

                            <SelectControl
                                label={__('Order', 'ontap')}
                                value={attributes.order}
                                options={[
                                    { label: __('Ascending', 'ontap'), value: 'ASC' },
                                    { label: __('Descending', 'ontap'), value: 'DESC' }
                                ]}
                                onChange={(value) => setAttributes({ order: value })}
                            />
                        </PanelBody>

                        {/* Controls */}
                        <PanelBody title={__('Controls', 'ontap')} initialOpen={false}>
                            <ToggleControl
                                label={__('Show Style Filters', 'ontap')}
                                checked={attributes.showFilters}
                                onChange={(value) => setAttributes({ showFilters: value })}
                            />

                            <ToggleControl
                                label={__('Show Search', 'ontap')}
                                checked={attributes.showSearch}
                                onChange={(value) => setAttributes({ showSearch: value })}
                            />

                            <ToggleControl
                                label={__('Show Sort', 'ontap')}
                                checked={attributes.showSort}
                                onChange={(value) => setAttributes({ showSort: value })}
                            />
                        </PanelBody>

                        {/* Display Options */}
                        <PanelBody title={__('Display Options', 'ontap')} initialOpen={false}>
                            <ToggleControl
                                label={__('Show Image', 'ontap')}
                                checked={attributes.showImage}
                                onChange={(value) => setAttributes({ showImage: value })}
                            />

                            <ToggleControl
                                label={__('Show Tap Number', 'ontap')}
                                checked={attributes.showTapNumber}
                                onChange={(value) => setAttributes({ showTapNumber: value })}
                            />

                            <ToggleControl
                                label={__('Show Style', 'ontap')}
                                checked={attributes.showStyle}
                                onChange={(value) => setAttributes({ showStyle: value })}
                            />

                            <ToggleControl
                                label={__('Show ABV', 'ontap')}
                                checked={attributes.showAbv}
                                onChange={(value) => setAttributes({ showAbv: value })}
                            />

                            <ToggleControl
                                label={__('Show IBU', 'ontap')}
                                checked={attributes.showIbu}
                                onChange={(value) => setAttributes({ showIbu: value })}
                            />

                            <ToggleControl
                                label={__('Show Description', 'ontap')}
                                checked={attributes.showDescription}
                                onChange={(value) => setAttributes({ showDescription: value })}
                            />

                            <ToggleControl
                                label={__('Show Containers/Pricing', 'ontap')}
                                checked={attributes.showContainers}
                                onChange={(value) => setAttributes({ showContainers: value })}
                            />
                        </PanelBody>
                    </InspectorControls>

                    <ServerSideRender
                        block="ontap/taplist"
                        attributes={attributes}
                    />
                </div>
            );
        },

        save: function() {
            // Server-side rendering, so return null
            return null;
        }
    });
})();
