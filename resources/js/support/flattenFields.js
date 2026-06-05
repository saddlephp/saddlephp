/**
 * Walk a serialized form tree and return its leaf field objects depth-first.
 *
 * The tree mixes leaf fields (carry a `component`) with layout containers
 * (carry a `layout`). Containers nest their children under `schema`, except
 * Tabs, which nest under `tabs` where each tab entry has its own `schema` and
 * no `layout` discriminator. A flat form is just an array of leaves and falls
 * through unchanged.
 *
 * @param {Array<Object>} nodes
 * @returns {Array<Object>} leaf field objects in document order
 */
export function flattenFields(nodes) {
    const leaves = [];

    for (const node of nodes ?? []) {
        if (node == null) continue;

        if (node.layout === 'tabs') {
            for (const tab of node.tabs ?? []) {
                leaves.push(...flattenFields(tab.schema));
            }
            continue;
        }

        if (node.layout) {
            leaves.push(...flattenFields(node.schema));
            continue;
        }

        leaves.push(node);
    }

    return leaves;
}

/** True when this leaf is a file upload (its useForm key needs special submit handling). */
export function isFileField(field) {
    return field?.component === 'file-field';
}
