(function (blocks, blockEditor, components, element, htmlEntities, i18n, serverSideRender) {
  'use strict';

  var el = element.createElement;
  var Fragment = element.Fragment;
  var useEffect = element.useEffect;
  var useState = element.useState;
  var InspectorControls = blockEditor.InspectorControls;
  var useBlockProps = blockEditor.useBlockProps;
  var PanelBody = components.PanelBody;
  var FormTokenField = components.FormTokenField;
  var RangeControl = components.RangeControl;
  var Notice = components.Notice;
  var decodeEntities = htmlEntities.decodeEntities;
  var __ = i18n.__;
  var ServerSideRender = serverSideRender;

  // Editor control for searching and selecting categories or tags.
  function TaxonomyTokenField(props) {
    var selected = props.value || [];
    var stateTerms = useState({});
    var termsBySlug = stateTerms[0];
    var setTermsBySlug = stateTerms[1];
    var stateSuggestions = useState([]);
    var suggestions = stateSuggestions[0];
    var setSuggestions = stateSuggestions[1];
    var stateError = useState(false);
    var error = stateError[0];
    var setError = stateError[1];
    var feedUrl = window.ucfNewsBlock && window.ucfNewsBlock.feedUrl
      ? window.ucfNewsBlock.feedUrl
      : '';

    function addTermsToMap(terms) {
      setTermsBySlug(function (current) {
        var next = Object.assign({}, current);

        terms.forEach(function (term) {
          if (term && term.slug) {
            next[term.slug] = decodeEntities(term.name || term.slug);
          }
        });

        return next;
      });
    }

    // Resolve saved slugs so existing blocks display human-readable token names.
    useEffect(function () {
      var controller;
      var query;
      var url;

      if (!feedUrl || !selected.length) {
        return function () {};
      }

      controller = new window.AbortController();
      query = new window.URLSearchParams();
      query.set('slug', selected.join(','));
      query.set('per_page', '100');
      query.set('_fields', 'slug,name');
      url = feedUrl + props.taxonomy + '?' + query.toString();

      window.fetch(url, { signal: controller.signal })
        .then(function (response) {
          if (!response.ok) {
            throw new Error('Request failed');
          }
          return response.json();
        })
        .then(function (data) {
          addTermsToMap(Array.isArray(data) ? data : []);
          setError(false);
        })
        .catch(function (requestError) {
          if (requestError.name !== 'AbortError') {
            setError(true);
          }
        });

      return function () {
        controller.abort();
      };
    }, [feedUrl, props.taxonomy, selected.join(',')]);

    function searchTerms(input) {
      var controller = searchTerms.controller;
      var timer = searchTerms.timer;

      if (controller) {
        controller.abort();
      }
      if (timer) {
        window.clearTimeout(timer);
      }

      if (!input) {
        setSuggestions([]);
        setError(false);
        return;
      }

      if (!feedUrl) {
        setSuggestions([]);
        setError(true);
        return;
      }

      searchTerms.timer = window.setTimeout(function () {
        var query = new window.URLSearchParams();
        var url;

        searchTerms.controller = new window.AbortController();
        query.set('search', input);
        query.set('per_page', '20');
        query.set('orderby', 'count');
        query.set('order', 'desc');
        query.set('_fields', 'slug,name');
        url = feedUrl + props.taxonomy + '?' + query.toString();

        window.fetch(url, { signal: searchTerms.controller.signal })
          .then(function (response) {
            if (!response.ok) {
              throw new Error('Request failed');
            }
            return response.json();
          })
          .then(function (data) {
            var terms = Array.isArray(data) ? data : [];
            var names = terms.map(function (term) {
              return decodeEntities(term.name || term.slug);
            });

            addTermsToMap(terms);
            setSuggestions(names);
            setError(false);
          })
          .catch(function (requestError) {
            if (requestError.name !== 'AbortError') {
              setSuggestions([]);
              setError(true);
            }
          });
      }, 300);
    }

    function changeTokens(names) {
      var slugs = names.map(function (name) {
        var match = Object.keys(termsBySlug).find(function (slug) {
          return termsBySlug[slug] === name;
        });

        if (match) {
          return match;
        }

        // Preserve an existing saved slug while its display name is resolving.
        return selected.indexOf(name) !== -1 ? name : null;
      }).filter(function (slug) {
        return slug !== null;
      });

      props.onChange(slugs);
    }

    var values = selected.map(function (slug) {
      return termsBySlug[slug] || slug;
    });

    return el(
      'div',
      { className: 'ucf-news-feed-taxonomy-control' },
      el(FormTokenField, {
        label: props.label,
        value: values,
        suggestions: suggestions,
        onInputChange: searchTerms,
        onChange: changeTokens
      }),
      error ? el(
        Notice,
        { status: 'warning', isDismissible: false },
        __('Options could not be loaded. Please try again.', 'ucf-news')
      ) : null
    );
  }

  // Story markup is rendered by PHP so the editor and frontend share one layout source.

  // Register the dynamic UCF News Feed block.
  blocks.registerBlockType('ucf-news/news-feed', {
    edit: function (props) {
      var attributes = props.attributes;
      var setAttributes = props.setAttributes;
      var blockProps = useBlockProps ? useBlockProps({ className: 'ucf-news-feed-editor-preview' }) : { className: 'wp-block-ucf-news-news-feed ucf-news-feed-editor-preview' };

      return el(
        Fragment,
        null,
        el(
          InspectorControls,
          null,
          el(
            PanelBody,
            { title: __('Story Filters', 'ucf-news'), initialOpen: true },
            el(TaxonomyTokenField, {
              label: __('Categories', 'ucf-news'),
              taxonomy: 'categories',
              value: attributes.sections,
              onChange: function (sections) { setAttributes({ sections: sections }); }
            }),
            el(TaxonomyTokenField, {
              label: __('Tags', 'ucf-news'),
              taxonomy: 'tags',
              value: attributes.topics,
              onChange: function (topics) { setAttributes({ topics: topics }); }
            })
          ),
          el(
            PanelBody,
            { title: __('Feed Settings', 'ucf-news'), initialOpen: true },
            el(RangeControl, {
              label: __('Number of Stories', 'ucf-news'),
              value: attributes.limit || 6,
              onChange: function (limit) { setAttributes({ limit: limit || 6 }); },
              min: 1,
              max: 24
            })
          )
        ),
        el(
          'div',
          blockProps,
          el(ServerSideRender, {
            block: 'ucf-news/news-feed',
            attributes: attributes
          })
        )
      );
    },
    save: function () {
      return null;
    }
  });

  // Native Gutenberg block styles. Classic remains the default presentation.
  blocks.registerBlockStyle('ucf-news/news-feed', {
    name: 'classic',
    label: __('Classic', 'ucf-news'),
    isDefault: true
  });
  blocks.registerBlockStyle('ucf-news/news-feed', {
    name: 'modern',
    label: __('Modern', 'ucf-news')
  });
  blocks.registerBlockStyle('ucf-news/news-feed', {
    name: 'card',
    label: __('Card', 'ucf-news')
  });

})(
  window.wp.blocks,
  window.wp.blockEditor,
  window.wp.components,
  window.wp.element,
  window.wp.htmlEntities,
  window.wp.i18n,
  window.wp.serverSideRender
);
