(function (apiFetch, blocks, blockEditor, components, element, htmlEntities, i18n) {
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
  var Spinner = components.Spinner;
  var Notice = components.Notice;
  var decodeEntities = htmlEntities.decodeEntities;
  var __ = i18n.__;

  // Editor control for searching and selecting categories or tags.
  function TaxonomyControl(props) {
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

  // Loads and renders the story preview shown inside the block editor.
  function StoryPreview(props) {
    var attributes = props.attributes;
    var stateStories = useState([]);
    var stories = stateStories[0];
    var setStories = stateStories[1];
    var stateLoading = useState(true);
    var loading = stateLoading[0];
    var setLoading = stateLoading[1];
    var stateError = useState(false);
    var error = stateError[0];
    var setError = stateError[1];

    useEffect(function () {
      var cancelled = false;
      var query = new window.URLSearchParams();
      query.set('limit', attributes.limit || 6);

      if (attributes.sections && attributes.sections.length) {
        query.set('sections', attributes.sections.join(','));
      }
      if (attributes.topics && attributes.topics.length) {
        query.set('topics', attributes.topics.join(','));
      }

      setLoading(true);
      setError(false);

      apiFetch({ path: '/ucf-news/v1/stories?' + query.toString() })
        .then(function (data) {
          if (!cancelled) {
            setStories(Array.isArray(data) ? data : []);
          }
        })
        .catch(function () {
          if (!cancelled) {
            setStories([]);
            setError(true);
          }
        })
        .then(function () {
          if (!cancelled) {
            setLoading(false);
          }
        });

      return function () {
        cancelled = true;
      };
    }, [attributes.limit, (attributes.sections || []).join(','), (attributes.topics || []).join(',')]);

    if (loading) {
      return el(
        'div',
        { className: 'ucf-news-feed-editor-status' },
        el(Spinner),
        ' ',
        __('Loading UCF News stories…', 'ucf-news')
      );
    }

    if (error) {
      return el(
        Notice,
        { status: 'warning', isDismissible: false },
        __('UCF News stories could not be loaded. Please try again.', 'ucf-news')
      );
    }

    if (!stories.length) {
      return el('p', { className: 'ucf-news-feed-editor-status' }, __('No UCF News stories were found.', 'ucf-news'));
    }

    var className = attributes.className || '';
    var style = 'classic';

    if (className.indexOf('is-style-modern') !== -1) {
      style = 'modern';
    } else if (className.indexOf('is-style-card') !== -1) {
      style = 'card';
    }

    return el(
      Fragment,
      null,
      attributes.title ? el('h2', { className: 'ucf-news-feed-title' }, attributes.title) : null,
      el(
        'ul',
        { className: 'ucf-news-feed-list' },
        stories.map(function (story, index) {
          var itemClass = story.image ? 'ucf-news-feed-item' : 'ucf-news-feed-item ucf-news-feed-item--no-image';
          return el(
            'li',
            { className: itemClass, key: story.id || story.link || index },
            story.image ? el(
              'figure',
              { className: 'ucf-news-feed-thumbnail' },
              el('img', {
                src: story.image,
                alt: '',
                width: story.width || undefined,
                height: story.height || undefined
              })
            ) : null,
            el(
              'div',
              { className: 'ucf-news-feed-item-content' },
              style === 'modern' && story.section ? el('div', { className: 'ucf-news-feed-section' }, story.section) : null,
              story.link ? el(
                'a',
                {
                  className: 'ucf-news-feed-story-title',
                  href: story.link,
                  onClick: function (event) { event.preventDefault(); }
                },
                story.title
              ) : el(
                'span',
                { className: 'ucf-news-feed-story-title' },
                story.title
              ),
              style === 'modern' && story.excerpt ? el('div', { className: 'ucf-news-feed-excerpt' }, story.excerpt) : null,
              style === 'card' && story.date ? el('div', { className: 'ucf-news-feed-date' }, story.date) : null
            )
          );
        })
      )
    );
  }

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
            el(TaxonomyControl, {
              label: __('Categories', 'ucf-news'),
              taxonomy: 'categories',
              value: attributes.sections,
              onChange: function (sections) { setAttributes({ sections: sections }); }
            }),
            el(TaxonomyControl, {
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
        el('div', blockProps, el(StoryPreview, { attributes: attributes }))
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
  window.wp.apiFetch,
  window.wp.blocks,
  window.wp.blockEditor,
  window.wp.components,
  window.wp.element,
  window.wp.htmlEntities,
  window.wp.i18n
);
