import Step from "../components/Step"
import {i18n} from "../lang/"
import State from "../State";

export default class ProductStep extends Step
{
    /**
     * @inheritDoc
     */
    getTemplate(): string
    {
        const props = State.get('product');
        const image = props.image ? `<img src="${props.image}" alt/>` : ''

        return `
            <h2>${i18n('product.headline')}</h2>
            <div class="product">
                <div class="image">
                    ${image}
                </div>
                <div class="content">
                    <div class="title">${props.name}</div>
                    <div class="description">${props.description}</div>
                    <div class="version">${props.version}</div>
                </div>
            </div>
            <div class="actions">
                <button data-prev>${i18n('actions.back')}</button>
                <button data-next class="primary">${i18n('product.actions.install')}</button>
            </div>
        `
    }
}
