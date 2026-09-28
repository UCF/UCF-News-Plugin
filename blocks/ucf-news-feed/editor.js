(function (apiFetch, blocks, blockEditor, components, element, i18n) {
  'use strict';

  var el = element.createElement;
  var Fragment = element.Fragment;
  var useEffect = element.useEffect;
  var useState = element.useState;
  var InspectorControls = blockEditor.InspectorControls;
  var useBlockProps = blockEditor.useBlockProps;
  var PanelBody = components.PanelBody;
  var SearchControl = components.SearchControl;
  var CheckboxControl = components.CheckboxControl;
  var Button = components.Button;
  var RangeControl = components.RangeControl;
  var Spinner = components.Spinner;
  var Notice = components.Notice;
  var __ = i18n.__;

  // Editor control for searching and selecting categories or tags.
  function TaxonomyControl(props) {
    var selected = props.value || [];
    var stateSearch = useState('');
    var search = stateSearch[0];
    var setSearch = stateSearch[1];
    var stateResults = useState([]);
    var results = stateResults[0];
    var setResults = stateResults[1];
    var stateLoading = useState(false);
    var loading = stateLoading[0];
    var setLoading = stateLoading[1];
    var stateError = useState(false);
    var error = stateError[0];
    var setError = stateError[1];

    useEffect(function () {
      var cancelled = false;
      var timer;

      if (search.length < 2) {
        setResults([]);
        setError(false);
        return function () {};
      }

      timer = window.setTimeout(function () {
        var url = window.ajaxurl
          + '?action=' + encodeURIComponent(props.action)
          + '&format=json&q=' + encodeURIComponent(search);
        setLoading(true);
        setError(false);

        window.fetch(url, { credentials: 'same-origin' })
          .then(function (response) {
            if (!response.ok) {
              throw new Error('Request failed');
            }
            return response.json();
          })
          .then(function (data) {
            if (!cancelled) {
              setResults(Array.isArray(data) ? data : []);
            }
          })
          .catch(function () {
            if (!cancelled) {
              setResults([]);
              setError(true);
            }
          })
          .then(function () {
            if (!cancelled) {
              setLoading(false);
            }
          });
      }, 300);

      return function () {
        cancelled = true;
        window.clearTimeout(timer);
      };
    }, [search, props.action]);

    function toggle(slug, checked) {
      var next;
      if (checked) {
        next = selected.indexOf(slug) === -1 ? selected.concat([slug]) : selected;
      } else {
        next = selected.filter(function (item) { return item !== slug; });
      }
      props.onChange(next);
    }

    return el(
      'div',
      { className: 'ucf-news-feed-taxonomy-control' },
      el(SearchControl, {
        label: props.label,
        value: search,
        onChange: setSearch,
        placeholder: props.placeholder
      }),
      selected.length ? el(
        'div',
        { className: 'ucf-news-feed-selected-terms' },
        el('p', { className: 'ucf-news-feed-selected-label' }, __('Selected:', 'ucf-news')),
        selected.map(function (slug) {
          return el(Button, {
            key: slug,
            variant: 'secondary',
            isSmall: true,
            onClick: function () { toggle(slug, false); },
            className: 'ucf-news-feed-selected-term'
          }, slug + ' ×');
        })
      ) : null,
      loading ? el(Spinner) : null,
      error ? el(Notice, { status: 'warning', isDismissible: false }, __('Options could not be loaded. Please try again.', 'ucf-news')) : null,
      search.length > 0 && search.length < 2 ? el('p', { className: 'components-base-control__help' }, __('Enter at least two characters to search.', 'ucf-news')) : null,
      results.length ? el(
        'div',
        { className: 'ucf-news-feed-taxonomy-results' },
        results.map(function (term) {
          return el(CheckboxControl, {
            key: term.slug,
            label: term.name || term.slug,
            checked: selected.indexOf(term.slug) !== -1,
            onChange: function (checked) { toggle(term.slug, checked); }
          });
        })
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
              placeholder: __('Search categories…', 'ucf-news'),
              action: 'ucf-news-sections',
              value: attributes.sections,
              onChange: function (sections) { setAttributes({ sections: sections }); }
            }),
            el(TaxonomyControl, {
              label: __('Tags', 'ucf-news'),
              placeholder: __('Search tags…', 'ucf-news'),
              action: 'ucf-news-topics',
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
  window.wp.i18n
);
