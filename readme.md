Familiar Menus
=======

A simple but powerful menus plugin for Craft CMS build with a custom element type.

You add menus in the plugin settings and then you can add "nodes" to that menu from the "Menus" tab.  A node can link to any entry in the system (including singles) or use a custom url and title

One the node is added it can be nested using the standard element sturcures interface.

**NOTE: this is a very early release much more is coming**

![](https://raw.githubusercontent.com/familiar-studio/craft-menus/master/screenshots/example.png)

Templating
-------

to output the nodes in you template you you call the new craft.menus.getNodes method and pass in your menu handle.

Right now thats the only template variable available but more are coming soon.

And example using bootstrap navbar syntax, but you can output using any syntax you want.

```

<nav>
  <ul class="nav nav-pills">

    {% nav node in craft.menus.getNodes('mainMenu') %}

      <li role="presentation"  class="
        {{ node.children|length ? 'dropdown' }}
        {{ node.active ? 'active' }}">

        <a href="{{node.link}}"
          {% if node.children|length %}
            class="dropdown-toggle" data-toggle="dropdown"
          {% endif %}>

          {{ node.title }}
        </a>

        {% ifchildren %}

          <ul class="dropdown-menu" role="menu">
            {% children %}
          </ul>

        {% endifchildren %}
      </li>

    {% endnav %}
  </ul>
</nav>

```

Each node has the following properties

* node.link (relative url)
* node.title (text of the link)
* node.url (full url)
* node.active (returns true if the node or a childnode matches the current url)
* node.children (any child nodes)
* node.entry (returns entire node's entry and all attached fields)



Todo
-----
* Add variable that outputs entire menu in one tag, (based on bootstrap menu type pills, tabs and navbar)
* Add support for locales
* Add element action to bulk add entries to menu
