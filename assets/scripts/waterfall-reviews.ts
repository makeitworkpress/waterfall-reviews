/**
 * Entry point for the Waterfall Reviews front-end.
 *
 * All modules are bundled together and booted once the document is ready.
 */
import { ready } from './utils';
import { charts } from './components/charts';
import { comments } from './components/comments';
import { filter } from './components/filter';
import { tables } from './components/tables';

const App = {
    components: { charts, comments, filter, tables },
    initialize(): void {
        this.components.charts.initialize();
        this.components.comments.initialize();
        this.components.filter.initialize();
        this.components.tables.initialize();
    },
};

// Keep the application available in the global scope for backwards compatibility.
window.App = App;

// Boot the application once the document is ready.
ready(() => App.initialize());
